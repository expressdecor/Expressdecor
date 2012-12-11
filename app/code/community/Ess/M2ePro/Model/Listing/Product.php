<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Product extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const STATUS_NOT_LISTED = 0;
    const STATUS_SOLD = 1;
    const STATUS_LISTED = 2;
    const STATUS_STOPPED = 3;
    const STATUS_FINISHED = 4;
    const STATUS_UNKNOWN = 5;
    const STATUS_BLOCKED = 6;

    const STATUS_CHANGER_UNKNOWN = 0;
    const STATUS_CHANGER_SYNCH = 1;
    const STATUS_CHANGER_USER = 2;
    const STATUS_CHANGER_COMPONENT = 3;
    const STATUS_CHANGER_OBSERVER = 4;

    // ########################################

    /**
     * @var Ess_M2ePro_Model_Listing
     */
    protected $listingModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    protected $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        if ($this->getStatus() == self::STATUS_LISTED) {
            return true;
        }

        return false;
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $variations = $this->getVariations(true);
        foreach ($variations as $variation) {
            $variation->deleteInstance();
        }

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getComponentMode());
        $tempLog->addProductMessage($this->getData('listing_id'),
                                    $this->getData('product_id'),
                                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN,
                                    NULL,
                                    Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_LISTING,
                                    // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully deleted');
                                    'Item was successfully deleted',
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        $this->listingModel = NULL;
        $this->magentoProductModel = NULL;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        if (is_null($this->listingModel)) {
            $this->listingModel = Mage::helper('M2ePro/Component')->getComponentObject(
                $this->getComponentMode(),'Listing',$this->getData('listing_id')
            );
        }

        return $this->listingModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing $instance
     */
    public function setListing(Ess_M2ePro_Model_Listing $instance)
    {
         $this->listingModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        if ($this->magentoProductModel) {
            return $this->magentoProductModel;
        }

        return $this->magentoProductModel = Mage::getModel('M2ePro/Magento_Product')
                ->setStoreId($this->getListing()->getStoreId())
                ->setProductId($this->getData('product_id'));
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $instance)
    {
        $this->magentoProductModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_General
     */
    public function getGeneralTemplate()
    {
        return $this->getListing()->getGeneralTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getListing()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getListing()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getListing()->getSynchronizationTemplate();
    }

    // ########################################

    public function getVariations($asObjects = false, array $filters = array())
    {
        $variations = $this->getRelatedComponentItems(
            'Listing_Product_Variation','listing_product_id',$asObjects,$filters
        );

        if ($asObjects) {
            foreach ($variations as $variation) {
                /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                $variation->setListingProduct($this);
            }
        }

        return $variations;
    }

    // ########################################

    public function getListingId()
    {
        return (int)$this->getData('listing_id');
    }

    public function getProductId()
    {
        return (int)$this->getData('product_id');
    }

    //----------------------------------------

    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    public function getStatusChanger()
    {
        return (int)$this->getData('status_changer');
    }

    // ########################################

    public function _getOnlyVariationProductQty()
    {
        if ($this->getMagentoProduct()->isBundleType()) {
            return $this->_getBundleProductQty();
        }
        if ($this->getMagentoProduct()->isGroupedType()) {
            return $this->_getGroupedProductQty();
        }
        if ($this->getMagentoProduct()->isConfigurableType()) {
            return $this->_getConfigurableProductQty();
        }

        return 0;
    }

    //-----------------------------------------

    protected function _getConfigurableProductQty()
    {
        $product = $this->getMagentoProduct()->getProduct();
        $totalQty = 0;
        foreach ($product->getTypeInstance()->getUsedProducts() as $childProduct) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);
            $qty = $stockItem->getQty();
            if ($stockItem->getIsInStock() == 1) {
                $totalQty += $qty;
            }
        }
        return (int)floor($totalQty);
    }

    protected function _getGroupedProductQty()
    {
        $product = $this->getMagentoProduct()->getProduct();
        $totalQty = 0;
        foreach ($product->getTypeInstance()->getAssociatedProducts() as $childProduct) {
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct);
            $qty = $stockItem->getQty();
            if ($stockItem->getIsInStock() == 1) {
                $totalQty += $qty;
            }
        }
        return (int)floor($totalQty);
    }

    protected function _getBundleProductQty()
    {
        $product = $this->getMagentoProduct()->getProduct();

        // Prepare bundle options format usable for search
        $productInstance = $product->getTypeInstance(true);
        $optionCollection = $productInstance->getOptionsCollection($product);
        $optionsData = $optionCollection->getData();

        foreach ($optionsData as $singleOption) {
            // Save QTY, before calculate = 0
            $bundleOptionsArray[$singleOption['option_id']] = 0;
        }

        $selectionsCollection = $productInstance->getSelectionsCollection($optionCollection->getAllIds(), $product);
        $_items = $selectionsCollection->getItems();
        foreach ($_items as $_item) {
            $itemInfoAsArray = $_item->toArray();
            if (isset($bundleOptionsArray[$itemInfoAsArray['option_id']])) {
                // For each option item inc total QTY
                if ($itemInfoAsArray['stock_item']['is_in_stock'] != 1) {
                    // Skip get qty for options product that not in stock
                    continue;
                }
                $addQty = $itemInfoAsArray['stock_item']['qty'];
                // Only positive
                $bundleOptionsArray[$itemInfoAsArray['option_id']] += (($addQty < 0) ? 0 : $addQty);
            }
        }

        // Get min of qty product for all options
        $minQty = -1;
        foreach ($bundleOptionsArray as $singleBundle) {
            if ($singleBundle < $minQty || $minQty == -1) {
                $minQty = $singleBundle;
            }
        }

        $minQty < 0 && $minQty = 0;

        return (int)floor($minQty);
    }

    // ########################################

    public function isNotListed()
    {
        return $this->getStatus() == self::STATUS_NOT_LISTED;
    }

    public function isUnknown()
    {
        return $this->getStatus() == self::STATUS_UNKNOWN;
    }

    public function isBlocked()
    {
        return $this->getStatus() == self::STATUS_BLOCKED;
    }

    //----------------------------------------

    public function isListed()
    {
        return $this->getStatus() == self::STATUS_LISTED;
    }

    public function isSold()
    {
        return $this->getStatus() == self::STATUS_SOLD;
    }

    public function isStopped()
    {
        return $this->getStatus() == self::STATUS_STOPPED;
    }

    public function isFinished()
    {
        return $this->getStatus() == self::STATUS_FINISHED;
    }

    //----------------------------------------

    public function isListable()
    {
        return ($this->isNotListed() || $this->isSold() ||
                $this->isStopped() || $this->isFinished() ||
                $this->isUnknown()) &&
                !$this->isBlocked();
    }

    public function isRelistable()
    {
        return ($this->isSold() || $this->isStopped() ||
                $this->isFinished() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    public function isRevisable()
    {
        return ($this->isListed() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    public function isStoppable()
    {
        return ($this->isListed() || $this->isUnknown()) &&
                !$this->isBlocked();
    }

    // ########################################

    public function listAction(array $params = array())
    {
        return $this->getChildObject()->listAction($params);
    }

    public function relistAction(array $params = array())
    {
        return $this->getChildObject()->relistAction($params);
    }

    public function reviseAction(array $params = array())
    {
        return $this->getChildObject()->reviseAction($params);
    }

    public function stopAction(array $params = array())
    {
        return $this->getChildObject()->stopAction($params);
    }

    public function deleteAction(array $params = array())
    {
        return $this->getChildObject()->deleteAction($params);
    }

    // ########################################

    public function getChangedItemsByAttributes(array $attributes, $componentMode = NULL)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductChange')->getMainTable();
        $listingsProductsTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        $fields = array(
            'pc_id'=>'id',
            'pc_attribute'=>'attribute',
            'pc_value_old'=>'value_old',
            'pc_value_new'=>'value_new',
            'pc_count_changes'=>'count_changes'
        );
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->distinct()
                             ->from(array('pc' => $productsChangesTable),$fields)
                             ->join(array('lp' => $listingsProductsTable),'`pc`.`product_id` = `lp`.`product_id`','*')
                             ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductChange::ACTION_UPDATE)
                             ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        !is_null($componentMode) && $dbSelect->where("`lp`.`component_mode` = ?",(string)$componentMode);

        return Mage::getResourceModel('core/config')
                            ->getReadConnection()
                            ->fetchAll($dbSelect);
    }

    // ----------------------------------------

    public function getUniqueOptionsProductsIds()
    {
        $listingProductVariationTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation')
            ->getMainTable();
        $listingProductVariationOptionTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')
            ->getMainTable();

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
                ->from(array('lpv' => $listingProductVariationTable),array())
                ->join(
                    array('lpvo' => $listingProductVariationOptionTable),
                    '`lpv`.`id` = `lpvo`.`listing_product_variation_id`',
                    array('product_id')
                )
                ->where('`lpv`.`listing_product_id` = ?',(int)$this->getId());

        $optionsProductsIds = (array)Mage::getResourceModel('core/config')
                                            ->getReadConnection()
                                            ->fetchCol($dbSelect);

        if (count($optionsProductsIds) <= 0) {
            return array();
        }

        foreach ($optionsProductsIds as &$temp) {
            $temp = (int)$temp;
        }

        return array_unique($optionsProductsIds);
    }

    public function getUniqueOptionsProductsStatuses($uniqueOptionsProductsIds = NULL)
    {
        if (is_null($uniqueOptionsProductsIds)) {
            $uniqueOptionsProductsIds = $this->getUniqueOptionsProductsIds();
        }

        if (count($uniqueOptionsProductsIds) <= 0) {
            return array();
        }

        return  Mage::getModel('catalog/product_status')
                                    ->getProductStatus($uniqueOptionsProductsIds,
                                                       $this->getListing()->getStoreId());
    }

    public function getUniqueOptionsProductsStockAvailability($uniqueOptionsProductsIds = NULL)
    {
        if (is_null($uniqueOptionsProductsIds)) {
            $uniqueOptionsProductsIds = $this->getUniqueOptionsProductsIds();
        }

        if (count($uniqueOptionsProductsIds) <= 0) {
            return array();
        }

        $catalogInventoryTable = Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item');

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from(array('cisi' => $catalogInventoryTable),array('product_id','is_in_stock'))
                             ->where('cisi.product_id IN ('.implode(',',$uniqueOptionsProductsIds).')');

        return (array)Mage::getResourceModel('core/config')
                                            ->getReadConnection()
                                            ->fetchPairs($dbSelect);
    }

    // ########################################
}