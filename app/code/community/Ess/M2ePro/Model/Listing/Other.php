<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Other extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Account
     */
    private $accountModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    protected $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Other');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->accountModel = NULL;
        $temp && $this->marketplaceModel = NULL;
        $temp && $this->magentoProductModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->accountModel)) {
            $this->accountModel = Mage::helper('M2ePro/Component')->getComponentObject(
                $this->getComponentMode(),'Account',$this->getData('account_id')
            );
        }

        return $this->accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->accountModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {
            $this->marketplaceModel = Mage::helper('M2ePro/Component')->getComponentObject(
                $this->getComponentMode(),'Marketplace',$this->getData('marketplace_id')
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->marketplaceModel = $instance;
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

        if (is_null($this->getProductId())) {
            throw new Exception('Product id is not setted');
        }

        return $this->magentoProductModel = Mage::getModel('M2ePro/Magento_Product')
                ->setStoreId($this->getChildObject()->getRelatedStoreId())
                ->setProductId($this->getProductId());
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $instance)
    {
        $this->magentoProductModel = $instance;
    }

    // ########################################

    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    public function getProductId()
    {
        $temp = $this->getData('product_id');
        return is_null($temp) ? NULL : (int)$temp;
    }

    //---------------------------------------

    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    public function getStatusChanger()
    {
        return (int)$this->getData('status_changer');
    }

    // ########################################

    public function isNotListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
    }

    public function isUnknown()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
    }

    public function isBlocked()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;
    }

    //-----------------------------------------

    public function isListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
    }

    public function isSold()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
    }

    public function isStopped()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
    }

    public function isFinished()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;
    }

    //-----------------------------------------

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

    public function reviseAction(array $params = array())
    {
        return $this->getChildObject()->reviseAction($params);
    }

    public function relistAction(array $params = array())
    {
        return $this->getChildObject()->relistAction($params);
    }

    public function stopAction(array $params = array())
    {
        return $this->getChildObject()->stopAction($params);
    }

    // ########################################

    public function getUsingMarketplacesIds()
    {
        $tableName = Mage::getResourceModel('M2ePro/Listing_Other')->getMainTable();
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $dbSelect = $connRead->select()
                             ->from($tableName,new Zend_Db_Expr('DISTINCT `marketplace_id`'));

        return array_values($connRead->fetchCol($dbSelect));
    }

    public function getChangedItemsByAttributes(array $attributes, $componentMode = NULL)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductChange')->getMainTable();
        $listingsOthersTable = Mage::getResourceModel('M2ePro/Listing_Other')->getMainTable();

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
                             ->join(array('lo' => $listingsOthersTable),'`pc`.`product_id` = `lo`.`product_id`','*')
                             ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductChange::ACTION_UPDATE)
                             ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        !is_null($componentMode) && $dbSelect->where("`lo`.`component_mode` = ?",(string)$componentMode);

        return Mage::getResourceModel('core/config')
                            ->getReadConnection()
                            ->fetchAll($dbSelect);
    }

    // ########################################

    public static function unmapDeletedProduct($product)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product ?
                        (int)$product->getId() : (int)$product;

        $listingsOther = Mage::getModel('M2ePro/Listing_Other')
                                    ->getCollection()
                                    ->addFieldToFilter('product_id', $productId)
                                    ->getItems();

        foreach ($listingsOther as $listingOther) {
            $listingOther->unmapProduct(Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION);
        }
    }

    //-----------------------------------------

    public function mapProduct($productId, $logsInitiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN)
    {
        $this->addData(array('product_id'=>$productId))->save();
        $this->getChildObject()->afterMapProduct();

        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode($this->getComponentMode());
        $logModel->addProductMessage($this->getId(),
            $logsInitiator,
            NULL,
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_MAP_LISTING,
            // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully mapped');
            'Item was successfully mapped',
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
    }

    public function unmapProduct($logsInitiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN)
    {
        $this->getChildObject()->beforeUnmapProduct();
        $this->setData('product_id', NULL)->save();

        $logModel = Mage::getModel('M2ePro/Listing_Other_Log');
        $logModel->setComponentMode($this->getComponentMode());
        $logModel->addProductMessage($this->getId(),
            $logsInitiator,
            NULL,
            Ess_M2ePro_Model_Listing_Other_Log::ACTION_UNMAP_LISTING,
            // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully unmapped');
            'Item was successfully unmapped',
            Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
    }

    // ########################################
}