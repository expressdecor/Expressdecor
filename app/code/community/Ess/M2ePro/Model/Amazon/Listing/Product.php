<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const IS_AFN_CHANNEL_NO  = 0;
    const IS_AFN_CHANNEL_YES = 1;

    const IS_ISBN_GENERAL_ID_NO  = 0;
    const IS_ISBN_GENERAL_ID_YES = 1;

    const IS_UPC_WORLDWIDE_ID_NO  = 0;
    const IS_UPC_WORLDWIDE_ID_YES = 1;

    const GENERAL_ID_SEARCH_STATUS_NONE  = 0;
    const GENERAL_ID_SEARCH_STATUS_PROCESSING  = 1;
    const GENERAL_ID_SEARCH_STATUS_SET_MANUAL  = 2;
    const GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC  = 3;

    const EXISTANCE_CHECK_STATUS_NONE = 0;
    const EXISTANCE_CHECK_STATUS_NOT_FOUND = 1;
    const EXISTANCE_CHECK_STATUS_FOUND = 2;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing_Product');
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getParentObject()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
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

    public function getVariations($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getVariations($asObjects,$filters);
    }

    // ########################################

    public function getCategoryId()
    {
        return $this->getData('category_id');
    }

    //----------------------------------------

    public function getSku()
    {
        return $this->getData('sku');
    }

    public function getGeneralId()
    {
        return $this->getData('general_id');
    }

    public function getWorldwideId()
    {
        return $this->getData('worldwide_id');
    }

    //-----------------------------------------

    public function getGeneralIdSearchStatus()
    {
        return (int)$this->getData('general_id_search_status');
    }

    public function isGeneralIdSearchStatusNone()
    {
        return $this->getGeneralIdSearchStatus() == self::GENERAL_ID_SEARCH_STATUS_NONE;
    }

    public function isGeneralIdSearchStatusProcessing()
    {
        return $this->getGeneralIdSearchStatus() == self::GENERAL_ID_SEARCH_STATUS_PROCESSING;
    }

    public function isGeneralIdSearchStatusSetManual()
    {
        return $this->getGeneralIdSearchStatus() == self::GENERAL_ID_SEARCH_STATUS_SET_MANUAL;
    }

    public function isGeneralIdSearchStatusSetAutomatic()
    {
        return $this->getGeneralIdSearchStatus() == self::GENERAL_ID_SEARCH_STATUS_SET_AUTOMATIC;
    }

    //-----------------------------------------

    public function getExistanceCheckStatus()
    {
        return (int)$this->getData('existance_check_status');
    }

    public function isExistanceCheckNone()
    {
        return $this->getExistanceCheckStatus() == self::EXISTANCE_CHECK_STATUS_NONE;
    }

    public function isExistanceCheckNotFound()
    {
        return $this->getExistanceCheckStatus() == self::EXISTANCE_CHECK_STATUS_NOT_FOUND;
    }

    public function isExistanceCheckFound()
    {
        return $this->getExistanceCheckStatus() == self::EXISTANCE_CHECK_STATUS_FOUND;
    }

    //-----------------------------------------

    public function getGeneralIdSearchSuggestData()
    {
        $temp = $this->getData('general_id_search_suggest_data');
        return is_null($temp) ? array() : json_decode($temp,true);
    }

    //-----------------------------------------

    public function getOnlinePrice()
    {
        return (float)$this->getData('online_price');
    }

    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    //-----------------------------------------

    public function isAfnChannel()
    {
        return (int)$this->getData('is_afn_channel') == self::IS_AFN_CHANNEL_YES;
    }

    public function isIsbnGeneralId()
    {
        return (int)$this->getData('is_isbn_general_id') == self::IS_ISBN_GENERAL_ID_YES;
    }

    public function isUpcWorldwideId()
    {
        return (int)$this->getData('is_upc_worldwide_id') == self::IS_UPC_WORLDWIDE_ID_YES;
    }

    //-----------------------------------------

    public function getStartDate()
    {
        return $this->getData('start_date');
    }

    public function getEndDate()
    {
        return $this->getData('end_date');
    }

    // ########################################

    public function getAddingSku()
    {
        $temp = $this->getData('cache_adding_sku');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getAmazonGeneralTemplate()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::SKU_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::SKU_MODE_DEFAULT) {
            $result = $this->getMagentoProduct()->getSku();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::SKU_MODE_PRODUCT_ID) {
            $result = $this->getParentObject()->getProductId();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::SKU_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_sku',$result);

        return $result;
    }

    public function getAddingGeneralId()
    {
        $temp = $this->getData('cache_adding_general_id');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getAmazonGeneralTemplate()->getGeneralIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::GENERAL_ID_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_general_id',$result);

        return $result;
    }

    public function getAddingWorldwideId()
    {
        $temp = $this->getData('cache_adding_worldwide_id');

        if (!empty($temp)) {
            return $temp;
        }

        $result = '';
        $src = $this->getAmazonGeneralTemplate()->getWorldwideIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::WORLDWIDE_ID_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);
        $this->setData('cache_adding_worldwide_id',$result);

        return $result;
    }

    // ########################################

    public function getTitle()
    {
        $title = '';
        $src = $this->getAmazonDescriptionTemplate()->getTitleSource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Amazon_Template_Description::TITLE_MODE_PRODUCT:
                $title = $this->getMagentoProduct()->getName();
                break;

            case Ess_M2ePro_Model_Amazon_Template_Description::TITLE_MODE_CUSTOM:
                $title = Mage::getSingleton('M2ePro/Template_Description_Parser')->parseTemplate($src['template'],
                                            $this->getMagentoProduct()->getProduct());
                break;

            default:
                $title = $this->getMagentoProduct()->getName();
                break;
        }

        return $title;
    }

    public function getBrand()
    {
        $brand = '';
        $src = $this->getAmazonDescriptionTemplate()->getBrandSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_Description::BRAND_MODE_CUSTOM) {
            $brand = Mage::getSingleton('M2ePro/Template_Description_Parser')->parseTemplate($src['template'],
                                                                                             $this->getMagentoProduct()
                                                                                                  ->getProduct());
        }

        return $brand;
    }

    public function getManufacturer()
    {
        $manufacturer = '';
        $src = $this->getAmazonDescriptionTemplate()->getManufacturerSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_Description::MANUFACTURER_MODE_CUSTOM) {
            $manufacturer = Mage::getSingleton('M2ePro/Template_Description_Parser')
                                                                            ->parseTemplate($src['template'],
                                                                                            $this->getMagentoProduct()
                                                                                                 ->getProduct());
        }

        return $manufacturer;
    }

    // ########################################

    public function getCondition()
    {
        $result = '';
        $src = $this->getAmazonGeneralTemplate()->getConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_MODE_NOT_SET) {
            return NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_MODE_DEFAULT) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    public function getConditionNote()
    {
        $result = '';
        $src = $this->getAmazonGeneralTemplate()->getConditionNoteSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_NOTE_MODE_NOT_SET) {
            return NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::CONDITION_NOTE_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    public function getHandlingTime()
    {
        $result = 0;
        $src = $this->getAmazonGeneralTemplate()->getHandlingTimeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::HANDLING_TIME_MODE_NONE) {
            return $result;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::HANDLING_TIME_MODE_RECOMMENDED) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $result = (int)$result;
        $result < 1  && $result = 1;
        $result > 30  && $result = 30;

        return $result;
    }

    public function getRestockDate()
    {
        $result = '';
        $src = $this->getAmazonGeneralTemplate()->getRestockDateSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::RESTOCK_DATE_MODE_CUSTOM_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_General::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    // ########################################

    public function getPrice($returnSalePrice = false)
    {
        $price = 0;

        if ($returnSalePrice) {
            $src = $this->getAmazonSellingFormatTemplate()->getSalePriceSource();
        } else {
            $src = $this->getAmazonSellingFormatTemplate()->getPriceSource();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_NOT_SET) {
            return NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_NONE) {
            return $price;
        }

        if ($this->getMagentoProduct()->isProductWithVariations()) {

            $variations = $this->getVariations(true,
                                              array('delete' => Ess_M2ePro_Model_Listing_Product_Variation::DELETE_NO));

            if (count($variations) > 0) {

                $pricesList = array();
                foreach ($variations as $variation) {
                    /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                    $pricesList[] = $variation->getChildObject()->getPrice($returnSalePrice);
                }

                return count($pricesList) > 0 ? min($pricesList) : 0;
            }
        }

        $price = $this->getBaseProductPrice($src['mode'],$src['attribute'],$returnSalePrice);
        return $this->getSellingFormatTemplate()->parsePrice($price, $src['coefficient']);
    }

    public function getSalePrice()
    {
        return $this->getPrice(true);
    }

    //-----------------------------------------

    public function getSalePriceStartDate()
    {
        if ($this->getAmazonSellingFormatTemplate()->isSalePriceModeSpecial()) {
            return $this->getMagentoProduct()->getSpecialPriceFromDate();
        }

        $src = $this->getAmazonSellingFormatTemplate()->getSalePriceStartDateSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::DATE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function getSalePriceEndDate()
    {
        if ($this->getAmazonSellingFormatTemplate()->isSalePriceModeSpecial()) {

            $date = $this->getMagentoProduct()->getSpecialPriceToDate();

            $tempDate = new DateTime($date, new DateTimeZone('UTC'));
            $tempDate->modify('-1 day');
            $date = Mage::helper('M2ePro')->getDate($tempDate->format('U'));

            return $date;
        }

        $src = $this->getAmazonSellingFormatTemplate()->getSalePriceEndDateSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::DATE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    //-----------------------------------------

    public function getBaseProductPrice($mode, $attribute = '',$returnSalePrice = false)
    {
        $price = 0;

        switch ($mode) {

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $specialPrice = Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL;
                    $price = $this->getBaseGroupedProductPrice($specialPrice, $returnSalePrice);
                } else {
                    $price = $this->getMagentoProduct()->getSpecialPrice();
                    if (!$returnSalePrice) {
                        $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
                    }
                }
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_ATTRIBUTE:
                $price = $this->getMagentoProduct()->getAttributeValue($attribute);
                break;

            default:
            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_PRODUCT:
                if ($this->getMagentoProduct()->isGroupedType()) {
                    $productPrice = Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_PRODUCT;
                    $price = $this->getBaseGroupedProductPrice($productPrice, $returnSalePrice);
                } else {
                    $price = $this->getMagentoProduct()->getPrice();
                }
                break;
        }

        $price < 0 && $price = 0;

        return $price;
    }

    protected function getBaseGroupedProductPrice($priceType, $returnSalePrice = false)
    {
        $price = 0;

        $product = $this->getMagentoProduct()->getProduct();

        foreach ($product->getTypeInstance()->getAssociatedProducts() as $tempProduct) {

            $tempPrice = 0;
            $tempProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($tempProduct);

            switch ($priceType) {
                case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_PRODUCT:
                    $tempPrice = $tempProduct->getPrice();
                    break;
                case Ess_M2ePro_Model_Amazon_Template_SellingFormat::PRICE_SPECIAL:
                    if ($returnSalePrice) {
                        $tempPrice = $tempProduct->getProduct()->getSpecialPrice();
                    } else {
                        $tempPrice = $tempProduct->getSpecialPrice();
                        $tempPrice <= 0 && $tempPrice = $tempProduct->getPrice();
                    }
                    break;
            }

            $tempPrice = (float)$tempPrice;

            if ($tempPrice < $price || $price == 0) {
                $price = $tempPrice;
            }
        }

        $price < 0 && $price = 0;

        return $price;
    }

    // ########################################

    public function getQty($productMode = false)
    {
        // variation product or simple product with custom options and variation enabled
        if ($this->getMagentoProduct()->isProductWithVariations()) {

            $variations = $this->getVariations(true,
                                              array('delete' => Ess_M2ePro_Model_Listing_Product_Variation::DELETE_NO));

            if (count($variations) > 0) {

                $totalQty = 0;
                foreach ($variations as $variation) {
                    /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                    $totalQty += $variation->getChildObject()->getQty();
                }

                return (int)floor($totalQty);
            }
        }

        $qty = 0;
        $src = $this->getAmazonSellingFormatTemplate()->getQtySource();

        switch ($src['mode']) {
            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_SINGLE:
                if ($productMode) {
                    $qty = $this->_getProductGeneralQty();
                } else {
                    $qty = 1;
                }
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_NUMBER:
                if ($productMode) {
                    $qty = $this->_getProductGeneralQty();
                } else {
                    $qty = $src['value'];
                }
                break;

            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_ATTRIBUTE:
                $qty = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
                break;

            default:
            case Ess_M2ePro_Model_Amazon_Template_SellingFormat::QTY_MODE_PRODUCT:
                $qty = $this->_getProductGeneralQty();
                break;
        }

        return (int)floor($qty);
    }

    //-----------------------------------------

    protected function _getProductGeneralQty()
    {
        if ($this->getMagentoProduct()->isStrictVariationProduct()) {
            return $this->getParentObject()->_getOnlyVariationProductQty();
        }
        return (int)floor($this->getMagentoProduct()->getQty());
    }

    // ########################################

    public function listAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_LIST, $params);
    }

    public function relistAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_RELIST, $params);
    }

    public function reviseAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_REVISE, $params);
    }

    public function stopAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_STOP, $params);
    }

    public function deleteAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_DELETE, $params);
    }

    //-----------------------------------------

    protected function processDispatcher($action, array $params = array())
    {
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector')->getProductDispatcher();
        return $dispatcherObject->process($action, $this->getId(), $params);
    }

    // ########################################

    public function getSubmitFeedArray()
    {
        try {
            $categoryInstance = Mage::getModel('M2ePro/Amazon_Category')->loadInstance((int)$this->getCategoryId());
        } catch(LogicException $e) {
            return array();
        }

        $arrayXml = array();
        foreach ($categoryInstance->getSpecifics() as $specific) {

            $xpath = trim($specific['xpath'],'/');
            $xpathParts = explode('/',$xpath);

            $path = '';
            $isFirst = true;

            foreach ($xpathParts as $part) {
                list($tag,$index) = explode('-',$part);

                if (!$tag) {
                    continue;
                }

                $isFirst || $path .= '{"childNodes": ';
                $path .= "{\"$tag\": {\"$index\": ";
                $isFirst = false;
            }

            if ($specific['mode'] == 'none') {

                $path .= '[]';
                $path .= str_repeat('}',substr_count($path,'{'));

                $arrayXml = Mage::helper('M2ePro')->arrayReplaceRecursive(
                    $arrayXml,
                    json_decode($path,true)
                );

                continue;
            }

            $value = $specific['mode'] == 'custom_value'
                ? $specific['custom_value']
                : $this->getMagentoProduct()->getAttributeValue($specific['custom_attribute']);

            $specific['type'] == 'int' && $value = (int)$value;
            $specific['type'] == 'float' && $value = (float)$value;
            $specific['type'] == 'date_time' && $value = str_replace(' ','T',$value);

            $attributes = array();
            foreach (json_decode($specific['attributes'],1) as $i=>$attribute) {

                list($attributeName) = array_keys($attribute);

                $attributeData = $attribute[$attributeName];

                $attributeValue = $attributeData['mode'] == 'custom_value'
                    ? $attributeData['custom_value']
                    : $this->getMagentoProduct()->getAttributeValue($attributeData['custom_attribute']);

                $attributes[$i] = array(
                    'name' => str_replace(' ','',$attributeName),
                    'value' => $attributeValue,
                );
            }

            $attributes = json_encode($attributes);

            $path .= '%data%';
            $path .= str_repeat('}',substr_count($path,'{'));

            $path = str_replace(
                '%data%',
                "{\"value\": \"$value\",\"attributes\": $attributes}",
                $path
            );

            $arrayXml = Mage::helper('M2ePro')->arrayReplaceRecursive(
                $arrayXml,
                json_decode($path,true)
            );
        }

        return $arrayXml;
    }

    // ########################################
}