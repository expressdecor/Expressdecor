<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Templates_Relist extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 30;
    const PERCENTS_END = 40;
    const PERCENTS_INTERVAL = 10;

    private $_synchronizations = array();
    private $_checkedListingsProductsIds = array();

    //####################################

    public function __construct()
    {
        parent::__construct();
        $this->_synchronizations = Mage::helper('M2ePro')->getGlobalValue('synchTemplatesArray');
    }

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Relist Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Relist" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Relist" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // Relist immediatelied
        //---------------------
        $this->executeImmediately();
        //---------------------

        // Relist scheduled
        //---------------------
        $this->executeScheduled();
        //---------------------
    }

    //------------------------------------

    private function executeImmediately()
    {
        $this->immediatelyChangeEbayStatus();

        $this->_lockItem->setPercents(self::PERCENTS_START + 1*self::PERCENTS_INTERVAL/2);
        $this->_lockItem->activate();

        $this->immediatelyChangedProducts();
    }

    private function executeScheduled()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Synchronization templates with schedule');

        foreach ($this->_synchronizations as &$synchronization) {

            if (!$synchronization['instance']->getChildObject()->isRelistMode()) {
                continue;
            }

            if (!$synchronization['instance']->getChildObject()->isRelistShedule()) {
                continue;
            }

            if ($synchronization['instance']->getChildObject()->getRelistSheduleType() ==
                Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_SCHEDULE_TYPE_WEEK) {

                if (!$synchronization['instance']->getChildObject()->isRelistSheduleWeekDayNow() ||
                    !$synchronization['instance']->getChildObject()->isRelistSheduleWeekTimeNow()) {
                    continue;
                }
            }

            $this->scheduledListings($synchronization['listings']);
            $this->_lockItem->activate();
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function immediatelyChangeEbayStatus()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Immediately when eBay status inactive');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductChange = array();
        $attributesForProductChange[] = 'ebay_listing_product_status';
        //------------------------------------

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = Mage::getModel('M2ePro/Listing_Product')
            ->getChangedItemsByAttributes($attributesForProductChange, Ess_M2ePro_Helper_Component_Ebay::NICK);
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        foreach ($changedListingsProducts as $changedListingProduct) {

            $tempNewValue = explode('_status_',$changedListingProduct['pc_value_new']);

            if (!is_array($tempNewValue) || count($tempNewValue) != 2) {
                continue;
            }

            $tempListingProductId = (int)str_replace('listing_product_','',$tempNewValue[0]);

            if ($tempListingProductId != (int)$changedListingProduct['id']) {
                continue;
            }

            $changedListingProduct['pc_value_new'] = (int)$tempNewValue[1];

            if ((int)$changedListingProduct['pc_value_new'] == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                continue;
            }

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component_Ebay')
                ->getObject('Listing_Product',$changedListingProduct['id']);

            if ($listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistShedule()) {
                continue;
            }

            if (!$this->isMeetRelistRequirements($listingProduct)) {
                continue;
            }

            if ($listingProduct->isRelistable()) {

                $tempParams = array();
                if ($listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistSendData()) {
                    $tempParams = array('all_data'=>true);
                } else {
                    $tempParams = array('only_data'=>array('base'=>true));
                }

                $this->_runnerActions->setProduct(
                    $listingProduct,Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_RELIST,$tempParams
                );
            } else if ($listingProduct->isListable()) {
                $this->_runnerActions->setProduct(
                    $listingProduct,Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_LIST,array()
                );
            }
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function immediatelyChangedProducts()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Immediately when product was changed');

        // Get attributes for products changes
        //------------------------------------
        $attributesForProductChange = array();
        $attributesForProductChange[] = 'product_instance';
        //------------------------------------

        // Get changed listings products
        //------------------------------------
        $changedListingsProducts = Mage::getModel('M2ePro/Listing_Product')
            ->getChangedItemsByAttributes($attributesForProductChange, Ess_M2ePro_Helper_Component_Ebay::NICK);
        //------------------------------------

        // Filter only needed listings products
        //------------------------------------
        foreach ($changedListingsProducts as $changedListingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component_Ebay')
                ->getObject('Listing_Product',$changedListingProduct['id']);

            if ($listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistShedule()) {
                continue;
            }

            if (!$this->isMeetRelistRequirements($listingProduct)) {
                continue;
            }

            if ($listingProduct->isRelistable()) {

                $tempParams = array();
                if ($listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistSendData()) {
                    $tempParams = array('all_data'=>true);
                } else {
                    $tempParams = array('only_data'=>array('base'=>true));
                }

                $this->_runnerActions->setProduct(
                    $listingProduct,Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_RELIST,$tempParams
                );
            } else if ($listingProduct->isListable()) {
                $this->_runnerActions->setProduct(
                    $listingProduct,Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_LIST,array()
                );
            }
        }
        //------------------------------------

        // Get changed listings products variations options
        //------------------------------------
        $changedListingsProductsVariationsOptions = Mage::getModel('M2ePro/Listing_Product_Variation_Option')
            ->getChangedItemsByAttributes($attributesForProductChange, Ess_M2ePro_Helper_Component_Ebay::NICK);
        //------------------------------------

        // Filter only needed listings products variations options
        //------------------------------------
        foreach ($changedListingsProductsVariationsOptions as $changedListingProductVariationOption) {

            /** @var $listingProductVariationOption Ess_M2ePro_Model_Listing_Product_Variation_Option */
            $listingProductVariationOption = Mage::helper('M2ePro/Component_Ebay')
                ->getObject('Listing_Product_Variation_Option',$changedListingProductVariationOption['id']);

            if ($listingProductVariationOption->getSynchronizationTemplate()->getChildObject()->isRelistShedule()) {
                continue;
            }

            if (!$this->isMeetRelistRequirements($listingProductVariationOption->getListingProduct())) {
                continue;
            }

            if ($listingProductVariationOption->getListingProduct()->isRelistable()) {

                $tempParams = array();
                $tempListingProduct = $listingProductVariationOption->getListingProduct();
                if ($tempListingProduct->getSynchronizationTemplate()->getChildObject()->isRelistSendData()) {
                    $tempParams = array('all_data'=>true);
                } else {
                    $tempParams = array('only_data'=>array('base'=>true));
                }

                $this->_runnerActions->setProduct(
                    $tempListingProduct,
                    Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_RELIST,
                    $tempParams
                );
            } else if ($listingProductVariationOption->getListingProduct()->isListable()) {
                $this->_runnerActions->setProduct(
                    $listingProductVariationOption->getListingProduct(),
                    Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_LIST,
                    array()
                );
            }
        }
        //------------------------------------

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //------------------------------------

    private function scheduledListings(&$listings)
    {
        $listingsIds = array();

        foreach ($listings as &$listing) {

            /** @var $listing Ess_M2ePro_Model_Listing */

            if (!$listing->isSynchronizationNowRun()) {
                continue;
            }

            $listingsIds[] = (int)$listing->getId();
        }

        if (count($listingsIds) <= 0) {
            return;
        }

        $listingsProductsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingsProductsCollection->getSelect()
            ->where('`status` != '.(int)Ess_M2ePro_Model_Listing_Product::STATUS_LISTED)
            ->where('`listing_id` IN ('.implode(',',$listingsIds).')');

        $listingsProductsArray = $listingsProductsCollection->toArray();

        if ((int)$listingsProductsArray['totalRecords'] <= 0) {
            return;
        }

        foreach ($listingsProductsArray['items'] as $listingProductArray) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component_Ebay')
                ->getObject('Listing_Product',$listingProductArray['id']);

            if ($listingProduct->getSynchronizationTemplate()->getChildObject()->getRelistSheduleType() ==
                Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_SCHEDULE_TYPE_THROUGH &&
                !$this->isScheduleThroughNow($listingProduct)) {
                continue;
            }

            if (!$this->isMeetRelistRequirements($listingProduct)) {
                continue;
            }

            if ($listingProduct->isRelistable()) {

                $tempParams = array();
                if ($listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistSendData()) {
                    $tempParams = array('all_data'=>true);
                } else {
                    $tempParams = array('only_data'=>array('base'=>true));
                }

                $this->_runnerActions->setProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_RELIST,
                    $tempParams
                );
            } else if ($listingProduct->isListable()) {
                $this->_runnerActions->setProduct(
                    $listingProduct,
                    Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_LIST,
                    array()
                );
            }
        }
    }

    //####################################

    private function isMeetRelistRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->_checkedListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // eBay available status
        //--------------------
        if ($listingProduct->isListed()) {
            return false;
        }

        if (!$listingProduct->isListable() && !$listingProduct->isRelistable()) {
            return false;
        }

        if ($listingProduct->isNotListed() &&
            !$listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistListMode()) {
            return false;
        }

        if ($listingProduct->isRelistable() &&
            $this->_runnerActions->isExistProductAction(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_RELIST,
                array())
        ) {
            return false;
        } else if ($listingProduct->isListable() &&
            $this->_runnerActions->isExistProductAction(
                $listingProduct,
                Ess_M2ePro_Model_Connector_Server_Ebay_Item_Dispatcher::ACTION_LIST,
                array())
        ) {
            return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
            return false;
        }

        if(!$listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistMode()) {
            return false;
        }

        if ($listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistFilterUserLock() &&
            $listingProduct->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return false;
        }
        //--------------------

        $uniqueOptionsProductsIds = NULL;

        // Check filters
        //--------------------
        if($listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistStatusEnabled()) {

            if ($listingProduct->getMagentoProduct()->getStatus() !=
                Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                return false;
            } else {

                if (is_null($uniqueOptionsProductsIds)) {
                    $uniqueOptionsProductsIds = $listingProduct->getUniqueOptionsProductsIds();
                }

                if (count($uniqueOptionsProductsIds) > 0) {

                    $statusesTemp = $listingProduct->getUniqueOptionsProductsStatuses($uniqueOptionsProductsIds);
                    if ((int)min($statusesTemp) == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                        return false;
                    }
                }
            }
        }

        if($listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistIsInStock()) {

            if (!$listingProduct->getMagentoProduct()->getStockAvailability()) {
                return false;
            } else {

                if (is_null($uniqueOptionsProductsIds)) {
                    $uniqueOptionsProductsIds = $listingProduct->getUniqueOptionsProductsIds();
                }

                if (count($uniqueOptionsProductsIds) > 0) {

                    $stockAvailabilityTemp = $listingProduct
                                                ->getUniqueOptionsProductsStockAvailability($uniqueOptionsProductsIds);
                    if (!(int)max($stockAvailabilityTemp)) {
                        return false;
                    }
                }
            }
        }

        if($listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistWhenQtyHasValue()) {

            $result = false;
            $productQty = (int)$listingProduct->getChildObject()->getQty(true);

            $ebaySynchronizationTemplate = $listingProduct->getSynchronizationTemplate()->getChildObject();

            $typeQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getRelistWhenQtyHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                $result = true;
            }

            if (!$result) {
                return false;
            }
        }
        //--------------------

        return true;
    }

    private function isScheduleThroughNow(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $dateEnd = $listingProduct->getChildObject()->getEndDate();
        if (is_null($dateEnd) || $dateEnd == '') {
            return false;
        }

        $interval = 60;
        $metric = $listingProduct->getSynchronizationTemplate()->getChildObject()->getRelistSheduleThroughMetric();
        $value = (int)$listingProduct->getSynchronizationTemplate()->getChildObject()->getRelistSheduleThroughValue();

        if ($metric == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_SCHEDULE_THROUGH_METRIC_DAYS) {
            $interval = 60*60*24;
        }
        if ($metric == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_SCHEDULE_THROUGH_METRIC_HOURS) {
            $interval = 60*60;
        }
        if ($metric == Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_SCHEDULE_THROUGH_METRIC_MINUTES) {
            $interval = 60;
        }

        $interval = $interval*$value;
        $dateEnd = strtotime($dateEnd);

        if (Mage::helper('M2ePro')->getCurrentGmtDate(true) < $dateEnd + $interval) {
            return false;
        }

        return true;
    }

    //####################################
}