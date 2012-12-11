<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Quote_Item
{
    /** @var $quoteBuilder Ess_M2ePro_Model_Magento_Quote */
    private $quoteBuilder = NULL;

    /** @var $proxyItem Ess_M2ePro_Model_Order_Item_Proxy */
    private $proxyItem = NULL;

    /** @var $product Mage_Catalog_Model_Product */
    private $product = NULL;

    private $channelCurrencyPrice = 0;

    // ########################################

    public function setQuoteBuilder(Ess_M2ePro_Model_Magento_Quote $quoteBuilder)
    {
        $this->quoteBuilder = $quoteBuilder;

        return $this;
    }

    // ########################################

    public function setProxyItem(Ess_M2ePro_Model_Order_Item_Proxy $proxyItem)
    {
        $this->proxyItem = $proxyItem;

        return $this;
    }

    // ########################################

    public function getProduct()
    {
        if (!is_null($this->product)) {
            return $this->product;
        }

        if ($this->proxyItem->getProduct()->getTypeId() == Ess_M2ePro_Model_Magento_Product::TYPE_GROUPED) {
            $this->product = $this->getAssociatedGroupedProduct();

            if (is_null($this->product)) {
                throw new Exception('There is no associated products found for grouped product.');
            }
        } else {
            $this->product = $this->proxyItem->getProduct();

            if ($this->product->getTypeId() == Ess_M2ePro_Model_Magento_Product::TYPE_BUNDLE) {
                $this->product->setPriceType(Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_PARENT);
            }
        }

        // tax class id should be set before price calculation
        $this->product->setTaxClassId($this->getProductTaxClassId());

        $price = $this->getBaseCurrencyPrice();
        $this->product->setPrice($price);
        $this->product->setSpecialPrice($price);

        return $this->product;
    }

    //-----------------------------------------

    private function getAssociatedGroupedProduct()
    {
        $variation = $this->proxyItem->getLowerCasedVariation();
        $variationName = array_shift($variation);

        $associatedProducts = Mage::getModel('M2ePro/Magento_Product')
            ->setProduct($this->proxyItem->getProduct())
            ->getProductVariationsForOrder();

        foreach ($associatedProducts as $product) {
            $relatedProduct = NULL;

            if ($product instanceof Mage_Catalog_Model_Product) {
                $relatedProduct = $product;
            } else if (is_numeric($product)) {
                $relatedProduct = Mage::getModel('catalog/product')->load((int)$product);
            }

            if (is_null($relatedProduct) || !$relatedProduct->getId()) {
                continue;
            }

            // return product if it's name is equal to variation name
            // or if variation name is unavailable return first associated product
            if (is_null($variationName) || trim(strtolower($relatedProduct->getName())) == $variationName) {
                return $relatedProduct;
            }
        }

        return NULL;
    }

    // ########################################

    /**
     * Return product price without conversion to store base currency
     *
     * @return float
     */
    public function getChannelCurrencyPrice()
    {
        $this->calculateChannelCurrencyPrice();

        return $this->channelCurrencyPrice;
    }

    /**
     * Return product price converted to store base currency
     *
     * @return float
     */
    private function getBaseCurrencyPrice()
    {
        $this->calculateChannelCurrencyPrice();

        $currency = $this->quoteBuilder->getProxyOrder()->getCurrency();
        $store    = $this->quoteBuilder->getQuote()->getStore();
        $price    = $this->channelCurrencyPrice;

        if (in_array($currency, $store->getAvailableCurrencyCodes(true))) {
            $currencyConvertRate = $store->getBaseCurrency()->getRate($currency);
            $currencyConvertRate == 0 && $currencyConvertRate = 1;
            $price = $price / $currencyConvertRate;
        }

        return $price;
    }

    /**
     * Calculate product price based on tax information and account settings
     */
    private function calculateChannelCurrencyPrice()
    {
        if ($this->channelCurrencyPrice > 0) {
            return;
        }

        /** @var $taxCalculator Mage_Tax_Model_Calculation */
        $taxCalculator = Mage::getSingleton('tax/calculation');
        $this->channelCurrencyPrice = $this->proxyItem->getPrice();

        if ($this->needToAddTax()) {
            $this->channelCurrencyPrice += $taxCalculator
                ->calcTaxAmount($this->channelCurrencyPrice, $this->proxyItem->getTaxRate(), false, false);
        } elseif ($this->needToSubtractTax()) {
            $this->channelCurrencyPrice -= $taxCalculator
                ->calcTaxAmount($this->channelCurrencyPrice, $this->proxyItem->getTaxRate(), true, false);
        }

        $this->channelCurrencyPrice = round($this->channelCurrencyPrice, 2);
    }

    private function needToAddTax()
    {
        return $this->quoteBuilder->getProxyOrder()->isTaxModeNone() && $this->proxyItem->hasTax();
    }

    private function needToSubtractTax()
    {
        if (!$this->quoteBuilder->getProxyOrder()->isTaxModeChannel() &&
            !$this->quoteBuilder->getProxyOrder()->isTaxModeMixed()) {
            return false;
        }

        if (!$this->proxyItem->hasVat()) {
            return false;
        }

        /** @var $taxCalculator Mage_Tax_Model_Calculation */
        $taxCalculator = Mage::getSingleton('tax/calculation');
        $store = $this->quoteBuilder->getQuote()->getStore();

        $request = new Varien_Object();
        $request->setProductClassId($this->product->getTaxClassId());

        return $this->proxyItem->getTaxRate() != $taxCalculator->getStoreRate($request, $store);
    }

    //-----------------------------------------

    private function getProductTaxClassId()
    {
        $proxyOrder = $this->quoteBuilder->getProxyOrder();
        $taxRate = $this->proxyItem->getTaxRate();
        $hasRatesForCountry = Mage::getSingleton('M2ePro/Magento_Tax_Helper')->hasRatesForCountry(
            $this->quoteBuilder->getQuote()->getShippingAddress()->getCountryId()
        );

        if ($proxyOrder->isTaxModeNone()
            || ($proxyOrder->isTaxModeChannel() && $taxRate == 0)
            || ($proxyOrder->isTaxModeMagento() && !$hasRatesForCountry)
        ) {
            return Ess_M2ePro_Model_Magento_Product::TAX_CLASS_ID_NONE;
        }

        if ($proxyOrder->isTaxModeMagento()
            || $taxRate == 0
            || $taxRate == $this->getProductTaxRate()
        ) {
            return $this->product->getTaxClassId();
        }

        // Create tax rule according to channel tax rate
        // -------------------------
        /** @var $taxRuleBuilder Ess_M2ePro_Model_Magento_Tax_Rule_Builder */
        $taxRuleBuilder = Mage::getModel('M2ePro/Magento_Tax_Rule_Builder');
        $taxRuleBuilder->buildTaxRule(
            $taxRate,
            $this->quoteBuilder->getQuote()->getShippingAddress()->getCountryId(),
            $this->quoteBuilder->getQuote()->getCustomerTaxClassId()
        );

        $taxRule = $taxRuleBuilder->getRule();
        $productTaxClasses = $taxRule->getProductTaxClasses();
        // -------------------------

        return array_shift($productTaxClasses);
    }

    private function getProductTaxRate()
    {
        /** @var $taxCalculator Mage_Tax_Model_Calculation */
        $taxCalculator = Mage::getSingleton('tax/calculation');

        $request = $taxCalculator->getRateRequest(
            $this->quoteBuilder->getQuote()->getShippingAddress(),
            $this->quoteBuilder->getQuote()->getBillingAddress(),
            $this->quoteBuilder->getQuote()->getCustomerTaxClassId(),
            $this->quoteBuilder->getQuote()->getStore()
        );
        $request->setProductClassId($this->product->getTaxClassId());

        return $taxCalculator->getRate($request);
    }

    // ########################################

    public function getRequest()
    {
        $request = new Varien_Object();
        $request->setQty($this->proxyItem->getQty());

        // grouped and downloadable products doesn't have options
        if ($this->proxyItem->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ||
            $this->proxyItem->getProduct()->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            return $request;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($this->product);
        $options = $this->getOptions($magentoProduct);

        if ($magentoProduct->isSimpleType()) {
            !empty($options) && $request->setOptions($options);
        } else if ($magentoProduct->isBundleType()) {
            $request->setBundleOption($options);
        } else if ($magentoProduct->isConfigurableType()) {
            $request->setSuperAttribute($options);
        }

        return $request;
    }

    //-----------------------------------------

    private function getOptions(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $variation = $this->proxyItem->getLowerCasedVariation();
        $magentoOptions = $magentoProduct->getProductVariationsForOrder();

        // Variation info unavailable - return first value for each required option
        // ---------------
        if (empty($variation)) {

            $firstOptions = array();

            foreach ($magentoOptions as $option) {
                $firstOptions[$option['option_id']] = $option['values'][0]['value_id'];
            }

            return $firstOptions;
        }
        // ---------------

        // Map variation with magento options
        // ---------------
        $mappedOptions = array();

        foreach ($magentoOptions as $option) {
            $optionValueLabel = $this->getMappedOptionValueLabel($variation, $option['labels']);
            if ($optionValueLabel == '') {
                continue;
            }

            $optionValueId = $this->getMappedOptionValueId($optionValueLabel, $option['values']);
            if (is_null($optionValueId)) {
                continue;
            }

            $mappedOptions[$option['option_id']] = $optionValueId;
        }
        // ---------------

        return $mappedOptions;
    }

    /**
     * Return value label for mapped option if found, empty string otherwise
     *
     * @param array $variation
     * @param array $optionLabels
     *
     * @return string
     */
    private function getMappedOptionValueLabel(array $variation, array $optionLabels)
    {
        foreach ($optionLabels as $label) {
            if (isset($variation[$label])) {
                return $variation[$label];
            }
        }

        return '';
    }

    /**
     * Return value id for value label if found, null otherwise
     *
     * @param       $valueLabel
     * @param array $optionValues
     *
     * @return int|null
     */
    private function getMappedOptionValueId($valueLabel, array $optionValues)
    {
        foreach ($optionValues as $value) {
            if (in_array($valueLabel, $value['labels'])) {
                return $value['value_id'];
            }
        }

        return NULL;
    }

    // ########################################
}