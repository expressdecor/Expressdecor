<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Option extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing_Product_Variation_Option');
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getParentObject()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->getParentObject()->getListingProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product_Variation
     */
    public function getListingProductVariation()
    {
        return $this->getParentObject()->getListingProductVariation();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_General
     */
    public function getGeneralTemplate()
    {
        return $this->getParentObject()->getGeneralTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getParentObject()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getParentObject()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getParentObject()->getSynchronizationTemplate();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing
     */
    public function getAmazonListing()
    {
        return $this->getListing()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    public function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation
     */
    public function getAmazonListingProductVariation()
    {
        return $this->getListingProductVariation()->getChildObject();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_General
     */
    public function getAmazonGeneralTemplate()
    {
        return $this->getGeneralTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_SellingFormat
     */
    public function getAmazonSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description
     */
    public function getAmazonDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Synchronization
     */
    public function getAmazonSynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ########################################

    public function getSku()
    {
        if (!$this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            return $this->getMagentoProduct()->getSku();
        }

        $tempSku = '';

        $mainProduct = $this->getListingProduct()->getMagentoProduct()->getProduct();
        $simpleAttributes = $mainProduct->getOptions();

        foreach ($simpleAttributes as $tempAttribute) {

            if (!(bool)(int)$tempAttribute->getData('is_require')) {
                continue;
            }

            if (!in_array($tempAttribute->getType(), array('drop_down', 'radio', 'multiple', 'checkbox'))) {
                continue;
            }

            if (strtolower($tempAttribute->getData('default_title')) != strtolower($this->getParentObject()
                                                                                        ->getAttribute()) &&
                strtolower($tempAttribute->getData('store_title')) != strtolower($this->getParentObject()
                                                                                      ->getAttribute()) &&
                strtolower($tempAttribute->getData('title')) != strtolower($this->getParentObject()->getAttribute())) {
                continue;
            }

            foreach ($tempAttribute->getValues() as $tempOption) {

                if (strtolower($tempOption->getData('default_title')) != strtolower($this->getParentObject()
                                                                                         ->getOption()) &&
                    strtolower($tempOption->getData('store_title')) != strtolower($this->getParentObject()
                                                                                       ->getOption()) &&
                    strtolower($tempOption->getData('title')) != strtolower($this->getParentObject()->getOption())) {
                    continue;
                }

                if (!is_null($tempOption->getData('sku')) &&
                    $tempOption->getData('sku') !== false) {
                    $tempSku = $tempOption->getData('sku');
                }

                break 2;
            }
        }

        return $tempSku;
    }

    public function getQty()
    {
        $qty = 0;

        $src = $this->getAmazonSellingFormatTemplate()->getQtySource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_SINGLE:
                $qty = 1;
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_NUMBER:
                $qty = (int)$src['value'];
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $qty = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            default:
            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_PRODUCT:
                $qty = (int)$this->getMagentoProduct()->getQty();
                break;
        }

        if (!$this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            if (!$this->getMagentoProduct()->getStockAvailability() ||
                $this->getMagentoProduct()->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED)  {
                // Out of stock or disabled Item? Set QTY = 0
                $qty = 0;
            }
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    // ########################################

    public function getPrice($returnSalePrice = false)
    {
        $price = 0;

        // Configurable product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType()) {

            $price = $this->getBaseProductPrice($returnSalePrice);

        // Bundle product
        } else if ($this->getListingProduct()->getMagentoProduct()->isBundleType()) {

            $price = $this->getBaseProductPrice($returnSalePrice);

        // Simple with custom options
        } else if ($this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {

            $price = $this->getSimpleWithCustomOptionsPrice($returnSalePrice);

        // Grouped product
        } else if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {

            $price = $this->getBaseProductPrice($returnSalePrice);

        }

        $price < 0 && $price = 0;

        return $price;
    }

    //-----------------------------------------

    protected function getSimpleWithCustomOptionsPrice($returnSalePrice = false)
    {
        $price = 0;

        $mainProduct = $this->getListingProduct()->getMagentoProduct()->getProduct();
        $simpleAttributes = $mainProduct->getOptions();

        foreach ($simpleAttributes as $tempAttribute) {

            if (!(bool)(int)$tempAttribute->getData('is_require')) {
                continue;
            }

            if (!in_array($tempAttribute->getType(), array('drop_down', 'radio', 'multiple', 'checkbox'))) {
                continue;
            }

            $defaultTitle = $tempAttribute->getData('default_title');
            $storeTitle = $tempAttribute->getData('store_title');
            $title = $tempAttribute->getData('title');
            $attribute = $this->getParentObject()->getAttribute();
            if ((is_null($defaultTitle) || strtolower($defaultTitle) != strtolower($attribute)) &&
                (is_null($storeTitle) || strtolower($storeTitle) != strtolower($attribute)) &&
                (is_null($title) || strtolower($title) != strtolower($attribute))) {
                continue;
            }

            foreach ($tempAttribute->getValues() as $tempOption) {

                $defaultTitle = $tempOption->getData('default_title');
                $storeTitle = $tempOption->getData('store_title');
                $title = $tempOption->getData('title');
                $option = $this->getParentObject()->getOption();
                if ((is_null($defaultTitle) || strtolower($defaultTitle) != strtolower($option)) &&
                    (is_null($storeTitle) || strtolower($storeTitle) != strtolower($option)) &&
                    (is_null($title) || strtolower($title) != strtolower($option))) {
                    continue;
                }

                if (!is_null($tempOption->getData('price_type')) &&
                    $tempOption->getData('price_type') !== false) {

                    switch ($tempOption->getData('price_type')) {
                        case 'percent':
                            if ($returnSalePrice) {
                                $src = $this->getAmazonSellingFormatTemplate()->getSalePriceSource();
                            } else {
                                 $src = $this->getAmazonSellingFormatTemplate()->getPriceSource();
                            }
                            $basePrice = $this->getAmazonListingProduct()
                                              ->getBaseProductPrice($src['mode'],
                                                                    $src['attribute'],
                                                                    $returnSalePrice);
                            $price = ($basePrice * (float)$tempOption->getData('price')) / 100;
                            break;
                        case 'fixed':
                            $price = (float)$tempOption->getData('price');
                            break;
                    }
                }

                break 2;
            }
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getBaseProductPrice($returnSalePrice = false)
    {
        $price = 0;

        if ($returnSalePrice) {
            $src = $this->getAmazonSellingFormatTemplate()->getSalePriceSource();
        } else {
            $src = $this->getAmazonSellingFormatTemplate()->getPriceSource();
        }

        switch ($src['mode']) {

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL:
                if ($returnSalePrice) {
                    $price = $this->getMagentoProduct()->getProduct()->getSpecialPrice();
                } else {
                    $price = $this->getMagentoProduct()->getSpecialPrice();
                    $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                }
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            default:
            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_PRODUCT:
                $price = $this->getMagentoProduct()->getPrice();
                break;
        }

        $price < 0 && $price = 0;

        return $price;
    }

    // ########################################
}