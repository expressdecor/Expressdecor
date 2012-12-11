<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Quote
{
    /** @var $proxyOrder Ess_M2ePro_Model_Order_Proxy */
    private $proxyOrder = NULL;

    /** @var $quote Mage_Sales_Model_Quote */
    private $quote = NULL;

    // ########################################

    /**
     * Set proxy order object which provides interface for data retrieval
     *
     * @param Ess_M2ePro_Model_Order_Proxy $proxyOrder
     * @return Ess_M2ePro_Model_Magento_Quote
     */
    public function setProxyOrder(Ess_M2ePro_Model_Order_Proxy $proxyOrder)
    {
        $this->proxyOrder = $proxyOrder;

        return $this;
    }

    /**
     * Return proxy order object
     *
     * @return Ess_M2ePro_Model_Order_Proxy|null
     */
    public function getProxyOrder()
    {
        return $this->proxyOrder;
    }

    // ########################################

    /**
     * Return magento quote object
     *
     * @return Mage_Sales_Model_Quote|null
     */
    public function getQuote()
    {
        return $this->quote;
    }

    // ########################################

    /**
     * Build quote object
     *
     * @throws Exception
     */
    public function buildQuote()
    {
        try {
            // do not change invoke order
            // --------------------
            $this->initializeQuote();
            $this->initializeCustomer();
            $this->initializeAddresses();

            $this->configureStore();

            $this->initializeCurrency();
            $this->initializeShippingMethodData();
            $this->initializeQuoteItems();
            $this->initializePaymentMethodData();

            //$this->quote->setTotalsCollectedFlag(false);
            $this->quote->collectTotals()->save();
            $this->quote->reserveOrderId();
            // --------------------
        } catch (Exception $e) {
            $this->quote->setIsActive(false)->save();
            throw $e;
        }
    }

    // ########################################

    /**
     * Initialize quote objects
     */
    private function initializeQuote()
    {
        $this->quote = Mage::getModel('sales/quote');

        $this->quote->setCheckoutMethod($this->proxyOrder->getCheckoutMethod());
        $this->quote->setStore($this->proxyOrder->getStore());
        $this->quote->save();
    }

    // ########################################

    /**
     * Assign customer
     */
    private function initializeCustomer()
    {
        if ($this->proxyOrder->isCheckoutMethodGuest()) {
            $addressData = $this->proxyOrder->getAddressData();

            $this->quote->setCustomerId(null)
                        ->setCustomerEmail($this->proxyOrder->getBuyerEmail())
                        ->setCustomerFirstname($addressData['firstname'])
                        ->setCustomerLastname($addressData['lastname'])
                        ->setCustomerIsGuest(true)
                        ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        }

        $this->quote->assignCustomer($this->proxyOrder->getCustomer());
    }

    // ########################################

    /**
     * Initialize shipping and billing address data
     */
    private function initializeAddresses()
    {
        // ----------
        $billingAddress = $this->quote->getBillingAddress();
        $billingAddress->addData($this->proxyOrder->getAddressData());
        $billingAddress->implodeStreetAddress();

        $billingAddress->setLimitCarrier('m2eproshipping');
        $billingAddress->setShippingMethod('m2eproshipping_m2eproshipping');
        $billingAddress->setCollectShippingRates(true);
        // ----------

        // ----------
        $shippingAddress = $this->quote->getShippingAddress();
        $shippingAddress->setSameAsBilling(0); // maybe just set same as billing?
        $shippingAddress->addData($this->proxyOrder->getAddressData());
        $shippingAddress->implodeStreetAddress();

        $shippingAddress->setLimitCarrier('m2eproshipping');
        $shippingAddress->setShippingMethod('m2eproshipping_m2eproshipping');
        $shippingAddress->setCollectShippingRates(true);
        // ----------
    }

    // ########################################

    /**
     * Initialize currency and currency convert rate
     */
    private function initializeCurrency()
    {
        if ($this->proxyOrder->isCurrencyAllowedForConversion()) {
            $currentCurrency = Mage::getModel('directory/currency')->load($this->proxyOrder->getCurrency());
        } else {
            $currentCurrency = $this->quote->getStore()->getBaseCurrency();
        }

        $this->quote->getStore()->setData('current_currency', $currentCurrency);
    }

    // ########################################

    /**
     * Configure store (invoked only after address, customer and store initialization and before price calculations)
     */
    private function configureStore()
    {
        /** @var $storeConfigurator Ess_M2ePro_Model_Magento_Quote_Store_Configurator */
        $storeConfigurator = Mage::getModel('M2ePro/Magento_Quote_Store_Configurator');
        $storeConfigurator->setQuoteBuilder($this);
        $storeConfigurator->prepareStoreConfigForOrder();
    }

    // ########################################

    /**
     * Initialize quote items objects
     *
     * @throws Exception
     */
    private function initializeQuoteItems()
    {
        foreach ($this->proxyOrder->getItems() as $item) {
            /** @var $quoteItemBuilder Ess_M2ePro_Model_Magento_Quote_Item */
            $quoteItemBuilder = Mage::getModel('M2ePro/Magento_Quote_Item');
            $quoteItemBuilder
                ->setQuoteBuilder($this)
                ->setProxyItem($item);

            $product = $quoteItemBuilder->getProduct();
            $request = $quoteItemBuilder->getRequest();

            $this->quote->setItemsCount($this->quote->getItemsCount() + 1);
            $this->quote->setItemsQty((float)$this->quote->getItemsQty() + $request->getQty());

            $result = $this->quote->addProduct($product, $request);

            if (is_string($result)) {
                throw new Exception($result);
            }

            $quoteItem = $this->quote->getItemByProduct($product);

            if ($quoteItem !== false) {
                $quoteItem->setOriginalCustomPrice($quoteItemBuilder->getChannelCurrencyPrice());
                $quoteItem->setNoDiscount(1);
            }
        }
    }

    // ########################################

    /**
     * Initialize data for M2E Shipping Method
     */
    private function initializeShippingMethodData()
    {
        $shippingData = $this->proxyOrder->getShippingData();
        $shippingData['shipping_price'] = $this->calculateShippingPrice($shippingData['shipping_price']);

        Mage::helper('M2ePro')->unsetGlobalValue('shipping_data');
        Mage::helper('M2ePro')->setGlobalValue('shipping_data', $shippingData);
    }

    //-----------------------------------------

    /**
     * Calculate shipping price according to store config, account settings, currency rate
     *
     * @param $shippingPrice
     * @return float
     */
    private function calculateShippingPrice($shippingPrice)
    {
        /** @var $taxCalculator Mage_Tax_Model_Calculation */
        $taxCalculator = Mage::getSingleton('tax/calculation');

        if ($this->needToAddShippingTax()) {
            $taxAmount = $taxCalculator->calcTaxAmount($shippingPrice, $this->proxyOrder->getTaxRate(), false, false);
            $shippingPrice += $taxAmount;
        } elseif ($this->needToSubtractShippingTax()) {
            $taxAmount = $taxCalculator->calcTaxAmount($shippingPrice, $this->proxyOrder->getTaxRate(), true, false);
            $shippingPrice -= $taxAmount;
        }

        if (in_array($this->proxyOrder->getCurrency(), $this->quote->getStore()->getAvailableCurrencyCodes(true))) {
            $currencyRate = (float)$this->quote->getStore()->getBaseCurrency()->getRate(
                $this->proxyOrder->getCurrency()
            );
            $currencyRate == 0 && $currencyRate = 1;

            $shippingPrice = $shippingPrice / $currencyRate;
        }

        return round($shippingPrice, 2);
    }

    private function needToAddShippingTax()
    {
        return $this->proxyOrder->isTaxModeNone() && !$this->proxyOrder->isShippingPriceIncludesTax();
    }

    private function needToSubtractShippingTax()
    {
        if (!$this->proxyOrder->isTaxModeChannel() && !$this->proxyOrder->isTaxModeMixed()) {
            return false;
        }

        if (!$this->proxyOrder->isShippingPriceIncludesTax()) {
            return false;
        }

        /** @var $storeConfigurator Ess_M2ePro_Model_Magento_Quote_Store_Configurator */
        $storeConfigurator = Mage::getSingleton('M2ePro/Magento_Quote_Store_Configurator');
        $storeShippingTaxRate = $storeConfigurator->getStoreShippingTaxRate($this->quote->getStore());

        return $this->proxyOrder->getTaxRate() != $storeShippingTaxRate;
    }

    // ########################################

    /**
     * Initialize data for M2E Payment Method
     */
    private function initializePaymentMethodData()
    {
        $quotePayment = $this->quote->getPayment();
        $quotePayment->importData($this->proxyOrder->getPaymentData());
    }

    // ########################################
}