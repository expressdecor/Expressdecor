<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Product
{
    const TYPE_SIMPLE       = 'simple';
    const TYPE_CONFIGURABLE = 'configurable';
    const TYPE_BUNDLE       = 'bundle';
    const TYPE_GROUPED      = 'grouped';

    const GROUPED_PRODUCT_ATTRIBUTE_LABEL = 'Option';
    const THUMBNAIL_IMAGE_CACHE_TIME = 604800;

    const TAX_CLASS_ID_NONE = 0;

    // ########################################

    private $_productId = 0;

    private $_storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

    /**
     * @var Mage_Catalog_Model_Product
     */
    private $_productModel = NULL;

    private $cache = array();

    // ########################################

    /**
     * @param int|null $productId
     * @param int|null $storeId
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function loadProduct($productId = NULL, $storeId = NULL)
    {
        $productId = (is_null($productId)) ? $this->_productId : $productId;
        $storeId = (is_null($storeId)) ? $this->_storeId : $storeId;

        if ($productId <= 0) {
            throw new Exception('The Product ID is not set.');
        }

        $this->_productModel = Mage::getModel('catalog/product')
                                         ->setStoreId($storeId)
                                         ->load($productId);

        $this->setProductId($productId);
        $this->setStoreId($storeId);

        $this->cache = array();

        return $this;
    }

    // ########################################

    /**
     * @param int $productId
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function setProductId($productId)
    {
        $this->_productId = $productId;
        $this->cache = array();
        return $this;
    }

    public function getProductId()
    {
        return $this->_productId;
    }

    // ########################################

    /**
     * @param int $storeId
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        $this->cache = array();
        return $this;
    }

    public function getStoreId()
    {
        return $this->_storeId;
    }

    // ########################################

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if ($this->_productModel) {
            return $this->_productModel;
        }

        if ($this->_productId > 0) {
            $this->loadProduct();
            return $this->_productModel;
        }

        throw new Exception('Load instance first');
    }

    /**
     * @param Mage_Catalog_Model_Product $productModel
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function setProduct(Mage_Catalog_Model_Product $productModel)
    {
        $this->_productModel = $productModel;

        $this->setProductId($this->_productModel->getId());
        $this->setStoreId($this->_productModel->getStoreId());

        $this->cache = array();

        return $this;
    }

    // ########################################

    public static function getTypeIdByProductId($productId)
    {
        $productId = (int)$productId;
        $table  = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from($table,'type_id')
                             ->where('`entity_id` = ?',(int)$productId);

        return Mage::getResourceModel('core/config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);
    }

    public static function getNameByProductId($productId, $storeId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        // Prepare tables names
        //-----------------------------
        $catalogProductEntityVarCharTable  = Mage::getSingleton('core/resource')->getTableName(
            'catalog_product_entity_varchar'
        );
        $eavAttributeTable  = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
        //-----------------------------

        // Make query for select
        //-----------------------------
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()->from(array('cpev'=>$catalogProductEntityVarCharTable),array('name'=>'value'))
            ->join(
                array('ea'=>$eavAttributeTable),
                '`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\'',
                array()
            )
            ->where('`cpev`.`entity_id` = ?',(int)$productId)->where('`cpev`.`store_id` = ?',(int)$storeId);
        //-----------------------------

        // Get row of product name
        //-----------------------------
        $name = Mage::getResourceModel('core/config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);
        //-----------------------------

        if ($name) {
            return $name;
        }

        if ($storeId == Mage_Core_Model_App::ADMIN_STORE_ID) {
            return '';
        }

        // Make query for select
        //-----------------------------
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(array('cpev'=>$catalogProductEntityVarCharTable),array('name'=>'value'))
            ->join(
                array('ea'=>$eavAttributeTable),
                '`cpev`.`attribute_id` = `ea`.`attribute_id` AND `ea`.`attribute_code` = \'name\'',
                array()
            )
            ->where('`cpev`.`entity_id` = ?',(int)$productId)
            ->where('`cpev`.`store_id` = '.Mage_Core_Model_App::ADMIN_STORE_ID);
        //-----------------------------

        // Get row of product name
        //-----------------------------
        $name = Mage::getResourceModel('core/config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);
        //-----------------------------

        if ($name) {
            return $name;
        }

        return '';
    }

    public static function getSkuByProductId($productId)
    {
        // Prepare tables names
        //-----------------------------
        $catalogProductEntityTable  = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');
        //-----------------------------

        // Make query for select
        //-----------------------------
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from($catalogProductEntityTable,'sku')
                             ->where('`entity_id` = ?',(int)$productId);
        //-----------------------------

        // Get row of product sku
        //-----------------------------
        $name = Mage::getResourceModel('core/config')
                        ->getReadConnection()
                        ->fetchOne($dbSelect);
        //-----------------------------

        return $name;
    }

    public static function getQtyByProductId($productId)
    {
        return (int)Mage::getModel('cataloginventory/stock_item')
                        ->loadByProduct($productId)
                        ->getQty();
    }

    public static function getStockAvailabilityByProductId($productId)
    {
        return (int)Mage::getModel('cataloginventory/stock_item')
                        ->loadByProduct($productId)
                        ->getIsInStock();
    }

    public static function getStatusByProductId($productId, $storeId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        $status = Mage::getModel('catalog/product_status')
                        ->getProductStatus($productId,$storeId);

        if (is_array($status) && isset($status[$productId])) {
            $status = (int)$status[$productId];
        } else {
            $status = 0;
        }

        return $status;
    }

    // ########################################

    public function getTypeId()
    {
        if (isset($this->cache['type_id'])) {
            return $this->cache['type_id'];
        }

        $typeId = NULL;
        if (!$this->_productModel && $this->_productId > 0) {
            $typeId = self::getTypeIdByProductId($this->_productId);
        } else {
            $typeId = $this->getProduct()->getTypeId();
        }

        $this->cache['type_id'] = $typeId;

        return $typeId;
    }

    //-----------------------------------------

    public function isProductWithVariations()
    {
        return !$this->isProductWithoutVariations();
    }

    public function isProductWithoutVariations()
    {
        return $this->isSimpleTypeWithoutCustomOptions();
    }

    public function isStrictVariationProduct()
    {
        return $this->isConfigurableType() || $this->isBundleType() || $this->isGroupedType();
    }

    //-----------------------------------------

    public function isSimpleType()
    {
        return $this->getTypeId() == self::TYPE_SIMPLE;
    }

    public function isSimpleTypeWithCustomOptions()
    {
        if (!$this->isSimpleType()) {
            return false;
        }

        if (!isset($this->cache[__METHOD__.'_count_options'])) {
            $countOptions = count($this->getProduct()->getOptions());
            $this->cache[__METHOD__.'_count_options'] = $countOptions;
        } else {
            $countOptions = $this->cache[__METHOD__.'_count_options'];
        }

        return $countOptions > 0;
    }

    public function isSimpleTypeWithoutCustomOptions()
    {
        if (!$this->isSimpleType()) {
            return false;
        }

        return !$this->isSimpleTypeWithCustomOptions();
    }

    //-----------------------------------------

    public function isConfigurableType()
    {
        return $this->getTypeId() == self::TYPE_CONFIGURABLE;
    }

    public function isBundleType()
    {
        return $this->getTypeId() == self::TYPE_BUNDLE;
    }

    public function isGroupedType()
    {
        return $this->getTypeId() == self::TYPE_GROUPED;
    }

    // ########################################

    public function getSku()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            $temp = self::getSkuByProductId($this->_productId);
            if (!is_null($temp) && $temp != '') {
                return $temp;
            }
        }
        return $this->getProduct()->getSku();
    }

    public function getName()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            return self::getNameByProductId($this->_productId, $this->_storeId);
        }
        return $this->getProduct()->getName();
    }

    //-----------------------------------------

    public function getStatus()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            $temp = self::getStatusByProductId($this->_productId, $this->_storeId);
            if ($temp == Mage_Catalog_Model_Product_Status::STATUS_DISABLED ||
                $temp == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                return $temp;
            }
        }
        return (int)$this->getProduct()->getStatus();
    }

    public function getStockAvailability()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            $temp = self::getStockAvailabilityByProductId($this->_productId);
            if ($temp == 0 || $temp == 1) {
                return $temp;
            }
        }
        return (int)$this->getProduct()->getStockItem()->getIsInStock();
    }

    // ########################################

    public function getPrice()
    {
        return (double)$this->getProduct()->getPrice();
    }

    public function setPrice($value)
    {
        return $this->getProduct()->setPrice($value);
    }

    //-----------------------------------------

    public function getSpecialPrice()
    {
        $fromDate = strtotime($this->getSpecialPriceFromDate());
        $toDate = strtotime($this->getSpecialPriceToDate());

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        if ($currentTimeStamp < $fromDate || $currentTimeStamp > $toDate) {
            return 0;
        }

        return (double)$this->getProduct()->getSpecialPrice();
    }

    public function setSpecialPrice($value)
    {
        return $this->getProduct()->setSpecialPrice($value);
    }

    //-----------------------------------------

    public function getSpecialPriceFromDate()
    {
        $fromDate = $this->getProduct()->getSpecialFromDate();

        if (is_null($fromDate) || $fromDate === false || $fromDate == '') {
            $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
            $fromDate = Mage::helper('M2ePro')->getDate($currentTimeStamp - 60*60*24*30*12);
        }

        return $fromDate;
    }

    public function getSpecialPriceToDate()
    {
        $toDate = $this->getProduct()->getSpecialToDate();

        if (is_null($toDate) || $toDate === false || $toDate == '') {
            $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
            $toDate = Mage::helper('M2ePro')->getDate($currentTimeStamp + 60*60*24*30*12);
        } else {
            $toDate = new DateTime($toDate, new DateTimeZone('UTC'));
            $toDate->modify('+1 day');
            $toDate = Mage::helper('M2ePro')->getDate($toDate->format('U'));
        }

        return $toDate;
    }

    //-----------------------------------------

    public function getQty()
    {
        if (!$this->_productModel && $this->_productId > 0) {
            return self::getQtyByProductId($this->_productId);
        }
        return (int)Mage::getModel('cataloginventory/stock_item')
                            ->loadByProduct($this->getProduct())
                            ->getQty();
    }

    public function setQty($value)
    {
        Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($this->getProduct())
                ->setQty($value)
                ->save();
    }

    // ########################################

    public function getAttributeLabel($attributeCode)
    {
        $productObject = $this->getProduct();

        /** @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
        $attribute = $productObject->getResource()->getAttribute($attributeCode);

        if (!$attribute) {
            return '';
        }

        $label = $attribute->getStoreLabel($this->getStoreId());
        if ($label == '') {
            $label = $attribute->getFrontendLabel();
        }

        return $label;
    }

    public function getAttributeValue($attributeCode)
    {
        $productObject = $this->getProduct();

        $value = $productObject->getData($attributeCode);
        if (is_null($value) || is_bool($value) || is_array($value) || $value === '') {
            return '';
        }

        /** @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
        $attribute = $productObject->getResource()->getAttribute($attributeCode);

        if (!$attribute) {
            return '';
        }

        // SELECT and MULTISELECT
        if ($attribute->getFrontendInput() === 'select' || $attribute->getFrontendInput() === 'multiselect') {

            // User Attribute
            if ((int)$attribute->getData('is_user_defined') == 1) {

                $valueNew = '';
                $optionIds = (array)explode(',',$value);

                foreach ($optionIds as $optionId) {
                    $attributeOption = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                                    ->addFieldToFilter('main_table.option_id', array('in' => $optionId))
                                                    //->setIdFilter($optionId)
                                                    ->setStoreFilter($this->getStoreId())
                                                    ->load()->getFirstItem();
                    $valueNew != '' && $valueNew .= ', ';
                    $valueNew .= $attributeOption->getData('value');
                }

                $value = (string)$valueNew;
            }

        // DATE
        } else if ($attribute->getFrontendInput() == 'date') {
            $temp = explode(' ',$value);
            isset($temp[0]) && $value = (string)$temp[0];

        // YES NO
        }  else if ($attribute->getFrontendInput() == 'boolean') {
            (bool)$value ? $value = Mage::helper('M2ePro')->__('Yes') :
                           $value = Mage::helper('M2ePro')->__('No');

        // PRICE
        }  else if ($attribute->getFrontendInput() == 'price') {
            $value = (string)round($value, 2);

        // MEDIA IMAGE
        }  else if ($attribute->getFrontendInput() == 'media_image') {
            if ($value == 'no_selection') {
                $value = '';
            } else {
                if (!preg_match('((mailto\:|(news|(ht|f)tp(s?))\://){1}\S+)',$value)) {
                    $value = Mage::getSingleton('catalog/product_media_config')->getMediaUrl($value);
                    $value = str_replace('https://','http://',$value);
                }
            }
        }

        return is_string($value) ? $value : '';
    }

    // ########################################

    public function getThumbnailImageLink()
    {
        $eaTable = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
        $cpevTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                              ->select()
                              ->from(array('cpev'=>$cpevTable),'value')
                              ->joinInner(array('ea'=>$eaTable),'`ea`.`attribute_id` = `cpev`.`attribute_id`',array())
                              ->where('`cpev`.`store_id` = ?',(int)$this->getStoreId())
                              ->where('`cpev`.`entity_id` = ?',(int)$this->getProductId())
                              ->where('`ea`.`attribute_code` = \'thumbnail\'');

        $tempPath = (string)Mage::getResourceModel('core/config')->getReadConnection()->fetchOne($dbSelect);

        if ($tempPath == '' || $tempPath == 'no_selection' || $tempPath == '/') {

            $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                ->select()
                ->from(array('cpev'=>$cpevTable),'value')
                ->joinInner(array('ea'=>$eaTable),'`ea`.`attribute_id` = `cpev`.`attribute_id`',array())
                ->where('`cpev`.`store_id` = ?',Mage_Core_Model_App::ADMIN_STORE_ID)
                ->where('`cpev`.`entity_id` = ?',(int)$this->getProductId())
                ->where('`ea`.`attribute_code` = \'thumbnail\'');

            $tempPath = (string)Mage::getResourceModel('core/config')->getReadConnection()->fetchOne($dbSelect);

            if ($tempPath == '' || $tempPath == 'no_selection' || $tempPath == '/') {
                return NULL;
            }
        }

        $imagePathOriginal = Mage::getBaseDir('media').DS.'catalog/product'.$tempPath;

        if (!is_file($imagePathOriginal)) {
            return NULL;
        }

        $width = 100;
        $height = 100;

        $prefixResizedImage = 'resized-'.$width.'px-'.$height.'px-';
        $imagePathResized = dirname($imagePathOriginal).DS.$prefixResizedImage.basename($imagePathOriginal);

        if (is_file($imagePathResized)) {
            $currentTime = Mage::helper('M2ePro')->getCurrentGmtDate(true);
            if (filemtime($imagePathResized) + self::THUMBNAIL_IMAGE_CACHE_TIME > $currentTime) {
                return Mage::getSingleton('catalog/product_media_config')->getMediaUrl(
                    str_replace(basename($imagePathOriginal),$prefixResizedImage.basename($imagePathOriginal),$tempPath)
                );
            }
            @unlink($imagePathResized);
        }

        try {

            $imageObj = new Varien_Image($imagePathOriginal);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepFrame(FALSE);
            $imageObj->resize($width, $height);
            $imageObj->save($imagePathResized);

        } catch (Exception $exception) {
            return NULL;
        }

        if (!is_file($imagePathResized)) {
            return NULL;
        }

        return Mage::getSingleton('catalog/product_media_config')->getMediaUrl(
            str_replace(basename($imagePathOriginal),$prefixResizedImage.basename($imagePathOriginal),$tempPath)
        );
    }

    public function getImageLink($attribute = 'image')
    {
        if ($attribute == '') {
            return '';
        }

        $imageUrl = $this->getAttributeValue($attribute);
        $imageUrl = $this->prepareImageUrl($imageUrl);

        return $imageUrl;
    }

    public function getGalleryImagesLinks($limitImages = 0)
    {
        $limitImages = (int)$limitImages;

        if ($limitImages <= 0) {
            return array();
        }

        $galleryImages = $this->getProduct()->getData('media_gallery');

        if (!isset($galleryImages['images'])) {
            return array();
        }

        $images = array();

        $i = 0;
        foreach ($galleryImages['images'] as $galleryImage) {

            if ($i >= $limitImages) {
                break;
            }

            if ((bool)$galleryImage['disabled']) {
                continue;
            }

            $imageUrl = Mage::getSingleton('catalog/product_media_config')->getMediaUrl($galleryImage['file']);
            $images[] = $this->prepareImageUrl($imageUrl);
            $i++;
        }

        return $images;
    }

    private function prepareImageUrl($url)
    {
        if (!is_string($url) || $url == '') {
            return '';
        }

        return str_replace(array('https://', ' '), array('http://', '%20'), $url);
    }

    // ########################################

    public function getProductVariations()
    {
        $variations = array();
        $variationsSet = array();

        if ($this->isConfigurableType()) {

            $tempInfo = $this->_getConfigurableOptionsForVariation();
            isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
            isset($tempInfo['variations']) && $variations = $tempInfo['variations'];

        } else {

            if ($this->isSimpleType()) {

                $tempInfo = $this->_getCustomOptionsForVariation();
                isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
                isset($tempInfo['variations']) && $variations = $tempInfo['variations'];

            } else if ($this->isBundleType()) {

                $tempInfo = $this->_getBundleOptionsForVariation();
                isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
                isset($tempInfo['variations']) && $variations = $tempInfo['variations'];

            } elseif ($this->isGroupedType()) {

                $tempInfo = $this->_getGroupedOptionsForVariation();
                isset($tempInfo['set']) && $variationsSet = $tempInfo['set'];
                isset($tempInfo['variations']) && $variations = $tempInfo['variations'];
            }

            $variations = $this->prepareRawVariations($variations, $variationsSet);
        }

        return array(
            'set' => $variationsSet,
            'variations' => $variations,
        );
    }

    //-----------------------------------------

    protected function _getCustomOptionsForVariation()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_SIMPLE) {
            return array();
        }

        $variationOptionsTitle = array();
        $variationOptionsList = array();

        foreach ($product->getOptions() as $productCustomOptions) {

            if (!(bool)(int)$productCustomOptions->getData('is_require')) {
                continue;
            }

            if (in_array($productCustomOptions->getType(), array('drop_down', 'radio', 'multiple', 'checkbox'))) {

                $optionCombinationTitle = array();
                $possibleVariationProductOptions = array();

                $optionTitle = $productCustomOptions->getTitle();
                if ($optionTitle == '') {
                    $optionTitle = $productCustomOptions->getDefaultTitle();
                }

                foreach ($productCustomOptions->getValues() as $option) {

                    $optionCombinationTitle[] = $option->getTitle();

                    $possibleVariationProductOptions[] = array(
                        'product_id' => $product->getId(),
                        'product_type' => $product->getTypeId(),
                        'attribute' => $optionTitle,
                        'option' => $option->getTitle()
                    );
                }

                $variationOptionsTitle[$optionTitle] = $optionCombinationTitle;
                $variationOptionsList[] = $possibleVariationProductOptions;
            }
        }

        return array(
            'set' => $variationOptionsTitle,
            'variations' => $variationOptionsList
        );
    }

    protected function _getBundleOptionsForVariation()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_BUNDLE) {
            return array();
        }

        $productInstance = $product->getTypeInstance(true);
        $productInstance->setStoreFilter($this->getStoreId(), $product);
        $optionCollection = $productInstance->getOptionsCollection($product);

        $variationOptionsTitle = array();
        $variationOptionsList = array();

        foreach ($optionCollection as $singleOption) {

            if (!(bool)(int)$singleOption->getData('required')) {
                continue;
            }

            $optionCombinationTitle = array();
            $possibleVariationProductOptions = array();

            $selectionsCollectionItems = $productInstance->getSelectionsCollection(
                array(0 => $singleOption->getId()), $product
            )->getItems();

            $optionTitle = $singleOption->getTitle();
            if ($optionTitle == '') {
                $optionTitle = $singleOption->getDefaultTitle();
            }

            foreach ($selectionsCollectionItems as $item) {
                $optionCombinationTitle[] = $item->getName();
                $possibleVariationProductOptions[] = array(
                    'product_id' => $item->getProductId(),
                    'product_type' => $product->getTypeId(),
                    'attribute' => $optionTitle,
                    'option' => $item->getName()
                );
            }

            $variationOptionsTitle[$optionTitle] = $optionCombinationTitle;
            $variationOptionsList[] = $possibleVariationProductOptions;
        }

        return array(
            'set' => $variationOptionsTitle,
            'variations' => $variationOptionsList
        );
    }

    protected function _getGroupedOptionsForVariation()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_GROUPED) {
            return array();
        }

        $optionCombinationTitle = array();

        $possibleVariationProductOptions = array();
        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts();

        foreach ($associatedProducts as $singleProduct) {

            $optionCombinationTitle[] = $singleProduct->getName();

            $possibleVariationProductOptions[] = array(
                'product_id' => $singleProduct->getId(),
                'product_type' => $product->getTypeId(),
                'attribute' => self::GROUPED_PRODUCT_ATTRIBUTE_LABEL,
                'option' => $singleProduct->getName()
            );
        }

        $variationOptionsTitle[self::GROUPED_PRODUCT_ATTRIBUTE_LABEL] = $optionCombinationTitle;
        $variationOptionsList[] = $possibleVariationProductOptions;

        return array(
            'set' => $variationOptionsTitle,
            'variations' => $variationOptionsList
        );
    }

    protected function _getConfigurableOptionsForVariation()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_CONFIGURABLE) {
            return array();
        }

        $productTypeInstance = $product->getTypeInstance();
        $configurableProducts = $productTypeInstance->getUsedProducts(null, $product);
        $configurableAttributes = $productTypeInstance->getUsedProductAttributes();

        $variationOptionsTitle = array();
        $possibleVariationProductOptions = array();

        $allAttributesOptions = array();

        foreach ($configurableProducts as $item) {

            // get product depended information
            $specifics = array();
            $item->setStoreId($this->getStoreId());

            foreach ($configurableAttributes as $attribute) {

                $attribute->setStoreId($this->getStoreId());

                /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                $attributeLabel = ucfirst($attribute->getStoreLabel($this->getStoreId()));
                $attributeLabel == '' && $attributeLabel = $attribute->getFrontendLabel();

                if (!isset($allAttributesOptions[$attributeLabel])) {

                    if ($attribute->getFrontendInput() === 'select' ||
                        $attribute->getFrontendInput() === 'multiselect') {

                        $allAttributesOptions[$attributeLabel] =
                                $attribute->getSource()->getAllOptions(false);
                    }
                }

                $attributeValue = Mage::getModel('M2ePro/Magento_Product')
                                            ->setProduct($item)
                                            ->getAttributeValue($attribute->getAttributeCode());

                $specifics[] = array(
                    'product_id' => $item->getId(),
                    'product_type' => self::TYPE_CONFIGURABLE,
                    'attribute' => $attributeLabel,
                    'option' => $attributeValue
                );

                // Generate list of all options titles
                if (!isset($variationOptionsTitle[$attributeLabel])) {
                    $variationOptionsTitle[$attributeLabel] = array();
                }

                if (!in_array($attributeValue, $variationOptionsTitle[$attributeLabel])) {
                    $variationOptionsTitle[$attributeLabel][] = $attributeValue;
                }
            }

            $possibleVariationProductOptions[] = $specifics;
        }

        $finalSet = $variationOptionsTitle;

        try {

            foreach ($allAttributesOptions as &$optionsTemp) {
                foreach ($optionsTemp as &$optionTemp) {
                    if (!is_array($optionTemp) || !isset($optionTemp['label'])) {
                        throw new Exception();
                    }
                    $optionTemp = $optionTemp['label'];
                }
            }

            if (count($allAttributesOptions) <= 0) {
                throw new Exception();
            }

            foreach ($allAttributesOptions as $key => &$optionsTemp) {

                if (!isset($variationOptionsTitle[$key]) ||
                    !is_array($variationOptionsTitle[$key])) {
                    throw new Exception();
                }

                $optionsTemp = array_intersect($optionsTemp,$variationOptionsTitle[$key]);
                $optionsTemp = array_values($optionsTemp);
            }

            $finalSet = $allAttributesOptions;

        } catch (Exception $exception) {}

        return array(
            'set' => $finalSet,
            'variations' => $possibleVariationProductOptions
        );
    }

    //-----------------------------------------

    protected function prepareRawVariations($optionsScope, $set,
                                            $optionScopeIndex = 0)
    {
        $resultVariations = array();

        if (!isset($optionsScope[$optionScopeIndex])) {
            return $resultVariations;
        }

        $subVariations = $this->prepareRawVariations($optionsScope,$set,
                                                     $optionScopeIndex+1);

        if (count($subVariations) <= 0) {

            foreach ($optionsScope[$optionScopeIndex] as $option) {
                $resultVariations[] = array($option);
            }

            return $resultVariations;
        }

        foreach ($optionsScope[$optionScopeIndex] as $option) {

            if (!isset($set[$option['attribute']]) ||
                !in_array($option['option'],$set[$option['attribute']])) {
                continue;
            }

            foreach ($subVariations as $subVariation) {
                $subVariation[] = $option;
                $resultVariations[] = $subVariation;
            }
        }

        return $resultVariations;
    }

    // ########################################

    public function getProductVariationsForOrder()
    {
        if ($this->isSimpleType()) {
            return $this->_getCustomOptionsForOrder();
        }

        if ($this->isBundleType()) {
            return $this->_getBundleOptionsForOrder();
        }

        if ($this->isGroupedType()) {
            return $this->_getGroupedOptionsForOrder();
        }

        if ($this->isConfigurableType()) {
            return $this->_getConfigurableOptionsForOrder();
        }

        return array();
    }

    //-----------------------------------------

    protected function _getCustomOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_SIMPLE) {
            return array();
        }

        $customOptions = array();

        $productOptions = $product->getOptions();

        foreach ($productOptions as $option) {
            if (!(bool)(int)$option->getData('is_require')) {
                continue;
            }

            $customOption = array(
                'option_id' => $option->getData('option_id'),
                'values'    => array(),
                'labels'    => array(
                    trim(strtolower($option->getData('store_title'))),
                    trim(strtolower($option->getData('title'))),
                    trim(strtolower($option->getData('default_title')))
                )
            );

            $values = $option->getValues();

            foreach ($values as $value) {
                $customOption['values'][] = array(
                    'value_id' => $value->getData('option_type_id'),
                    'labels'   => array(
                        trim(strtolower($value->getData('store_title'))),
                        trim(strtolower($value->getData('title'))),
                        trim(strtolower($value->getData('default_title')))
                        //trim(strtolower($value->getData('sku')))
                    )
                );
            }

            $customOptions[] = $customOption;
        }

        return $customOptions;
    }

    protected function _getBundleOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_BUNDLE) {
            return array();
        }

        $bundleOptions = array();

        $productInstance = $product->getTypeInstance(true);
        $productInstance->setStoreFilter($this->getStoreId(), $product);

        $optionsCollection = $productInstance->getOptionsCollection($product);
        $selectionsCollection = $product->getTypeInstance(true)->getSelectionsCollection(
            $optionsCollection->getAllIds(), $product
        );

        foreach ($optionsCollection as $option) {
            if (!$option->getData('required')) {
                continue;
            }

            $bundleOption = array(
                'option_id' => $option->getData('option_id'),
                'values'    => array(),
                'labels'    => array(
                    trim(strtolower($option->getData('default_title'))),
                    trim(strtolower($option->getData('title')))
                )
            );

            foreach ($selectionsCollection as $selection) {
                if ($option->getData('option_id') != $selection->getData('option_id')) {
                    continue;
                }

                $bundleOption['values'][] = array(
                    'value_id'   => $selection->getData('selection_id'),
                    'labels'     => array(
                        trim(strtolower($selection->getData('name')))
                    )
                );
            }

            $bundleOptions[] = $bundleOption;
        }

        return $bundleOptions;
    }

    protected function _getGroupedOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_GROUPED) {
            return array();
        }

        $associatedProducts = $product->getTypeInstance()->getAssociatedProducts();

        if (count($associatedProducts)) {
            return $associatedProducts;
        }

        return $product->getTypeInstance()->getChildrenIds($product->getId());
    }

    protected function _getConfigurableOptionsForOrder()
    {
        $product = $this->getProduct();

        if ($product->getTypeId() != self::TYPE_CONFIGURABLE) {
            return array();
        }

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $product->getTypeInstance(true);
        $configurableOptions = array();

        foreach ($productTypeInstance->getConfigurableAttributes($product) as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $productAttribute->setStoreId($this->getStoreId());

            $configurableOptions[] = array(
                'option_id' => $attribute->getAttributeId(),
                'labels' => array(
                    trim(strtolower($attribute->getLabel())),
                    trim(strtolower($productAttribute->getFrontendLabel())),
                    trim(strtolower($productAttribute->getStoreLabel()))
                ),
                'values' => $this->getConfigurableAttributeValuesForOrder($attribute)
            );
        }

        return $configurableOptions;
    }

    //-----------------------------------------

    private function getConfigurableAttributeValuesForOrder($attribute)
    {
        $product = $this->getProduct();
        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $product->getTypeInstance(true);
        $productAttribute = $attribute->getProductAttribute();

        $options = $this->getConfigurableAttributeOptionsForOrder($productAttribute);
        $values = array();

        foreach ($options as $option) {
            foreach ($productTypeInstance->getUsedProducts(null, $product) as $associatedProduct) {
                if ($option['value_id'] != $associatedProduct->getData($productAttribute->getAttributeCode())) {
                    continue;
                }

                $attributeOptionKey = $attribute->getAttributeId() . ':' . $option['value_id'];
                if (!isset($values[$attributeOptionKey])) {
                    $values[$attributeOptionKey] = $option;
                }
            }
        }

        return array_values($values);
    }

    private function getConfigurableAttributeOptionsForOrder($productAttribute)
    {
        $options = $productAttribute->getSource()->getAllOptions(false, false);
        $defaultOptions = $productAttribute->getSource()->getAllOptions(false, true);

        $mergedOptions = array();
        foreach ($options as $option) {
            $mergedOption = array(
                'value_id' => $option['value'],
                'labels' => array(
                    trim(strtolower($option['label']))
                )
            );

            foreach ($defaultOptions as $defaultOption) {
                if ($defaultOption['value'] == $option['value']) {
                    $mergedOption['labels'][] = trim(strtolower($defaultOption['label']));
                    break;
                }
            }

            $mergedOptions[] = $mergedOption;
        }

        return $mergedOptions;
    }

    // ########################################
}