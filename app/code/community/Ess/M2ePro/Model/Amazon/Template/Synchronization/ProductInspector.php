<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_Synchronization_ProductInspector
{
    /**
     * @var Ess_M2ePro_Model_Amazon_Template_Synchronization_RunnerActions
     */
    protected $_runnerActions = NULL;

    private $_checkedRelistListingsProductsIds = array();
    private $_checkedStopListingsProductsIds = array();

    private $_checkedQtyListingsProductsIds = array();
    private $_checkedPriceListingsProductsIds = array();

    //####################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        if (isset($params['runner_actions'])) {
            $this->_runnerActions = $params['runner_actions'];
        } else {
            $runnerActionsModel = Mage::getModel('M2ePro/Amazon_Template_Synchronization_RunnerActions');
            $runnerActionsModel->removeAllProducts();
            $this->_runnerActions = $runnerActionsModel;
        }
    }

    //####################################

    public function processProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->processProducts(array($listingProduct));
    }

    public function processProducts(array $listingsProducts = array())
    {
        $this->_runnerActions->removeAllProducts();

        foreach ($listingsProducts as $listingProduct) {

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                continue;
            }

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $this->processItem($listingProduct);
        }

        $this->_runnerActions->execute();
        $this->_runnerActions->removeAllProducts();
    }

    //-----------------------------------

    private function processItem(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $synchGroup = '/synchronization/settings/templates/';
        $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                  ->getGroupValue($synchGroup,'mode');

        $amazonSynchGroup = '/amazon/synchronization/settings/templates/';
        $tempLocalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                 ->getGroupValue($amazonSynchGroup,'mode');

        if (!$tempGlobalMode || !$tempLocalMode) {
            return;
        }

        if ($listingProduct->isListed()) {

            $tempResult = false;

            // Check Stop Requirements
            //-------------------------------
            $amazonSynch = '/amazon/synchronization/settings/templates/stop/';
            $stopMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                ->getGroupValue($amazonSynch,'mode');
            if ($stopMode) {
                $tempResult = $this->isMeetStopRequirements($listingProduct);
                $tempResult && $this->_runnerActions
                                    ->setProduct($listingProduct,
                                                 Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_STOP,
                                                 array());
            }
            //-------------------------------

            // Check Revise Requirements
            //-------------------------------
            if (!$tempResult) {

                $amazonSynch = '/amazon/synchronization/settings/templates/revise/';
                $reviseMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                      ->getGroupValue($amazonSynch,'mode');

                if ($reviseMode) {
                    $this->inspectReviseQtyRequirements($listingProduct);
                    $this->inspectRevisePriceRequirements($listingProduct);
                }
            }
            //-------------------------------

        } else {

            $amazonSynch = '/amazon/synchronization/settings/templates/relist/';
            $relistMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                  ->getGroupValue($amazonSynch,'mode');

            if (!$relistMode) {
                return;
            }

            // Check Relist Requirements
            //-------------------------------
            $tempResult = $this->isMeetRelistRequirements($listingProduct);

            if ($tempResult) {

                if ($listingProduct->isStopped()) {

                    $this->_runnerActions
                         ->setProduct($listingProduct,
                                      Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_RELIST,
                                      array());

                } else if ($listingProduct->isNotListed()) {

                    $this->_runnerActions->setProduct($listingProduct,
                                                      Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_LIST,
                                                      array());
                }
            }
            //-------------------------------
        }
    }

    //####################################

    public function isMeetStopRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->_checkedStopListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedStopListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // Amazon available status
        //--------------------
        if (!$listingProduct->isListed() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isStoppable()) {
            return false;
        }

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_STOP,
                        array())
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
           return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
            return false;
        }
        //--------------------

        $uniqueOptionsProductsIds = NULL;

        // Check filters
        //--------------------
        if ($listingProduct->getSynchronizationTemplate()->getChildObject()->isStopStatusDisabled()) {

            if ($listingProduct->getMagentoProduct()->getStatus() ==
                Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                return true;
            } else {

                if (is_null($uniqueOptionsProductsIds)) {
                    $uniqueOptionsProductsIds = $listingProduct->getUniqueOptionsProductsIds();
                }

                if (count($uniqueOptionsProductsIds) > 0) {

                    $statusesTemp = $listingProduct->getUniqueOptionsProductsStatuses($uniqueOptionsProductsIds);
                    if ((int)min($statusesTemp) == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                        return true;
                    }
                }
            }
        }

        if ($listingProduct->getSynchronizationTemplate()->getChildObject()->isStopOutOfStock()) {

            if (!$listingProduct->getMagentoProduct()->getStockAvailability()) {
                return true;
            } else {

                if (is_null($uniqueOptionsProductsIds)) {
                    $uniqueOptionsProductsIds = $listingProduct->getUniqueOptionsProductsIds();
                }

                if (count($uniqueOptionsProductsIds) > 0) {

                    $stockAvailabilityTemp = $listingProduct
                                                ->getUniqueOptionsProductsStockAvailability($uniqueOptionsProductsIds);
                    if (!(int)max($stockAvailabilityTemp)) {
                        return true;
                    }
                }
            }
        }

        if ($listingProduct->getSynchronizationTemplate()->getChildObject()->isStopWhenQtyHasValue()) {

            $productQty = (int)$listingProduct->getChildObject()->getQty(true);
            $amazonSynchronizationTemplate = $listingProduct->getSynchronizationTemplate()->getChildObject();

            $typeQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyHasValueType();
            $minQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyHasValueMin();
            $maxQty = (int)$amazonSynchronizationTemplate->getStopWhenQtyHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_QTY_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }
        //--------------------

        return false;
    }

    public function isMeetRelistRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->_checkedRelistListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedRelistListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // Amazon available status
        //--------------------
        if ((!$listingProduct->isNotListed() && !$listingProduct->isStopped()) || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isListable() && !$listingProduct->isRelistable()) {
            return false;
        }

        if ($listingProduct->isNotListed() && !$listingProduct->getSynchronizationTemplate()
                                                              ->getChildObject()
                                                              ->isRelistListMode()
        ) {
            return false;
        }

        if ($listingProduct->isStopped() && $this->_runnerActions
                                                 ->isExistProductAction(
                                                    $listingProduct,
                                                    Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_RELIST,
                                                    array())
        ) {
            return false;
        } else if ($listingProduct->isNotListed() &&
                   $this->_runnerActions
                        ->isExistProductAction(
                               $listingProduct,
                               Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_LIST,
                       array())
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
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
        if ($listingProduct->getSynchronizationTemplate()->getChildObject()->isRelistShedule()) {
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

            $amazonSynchronizationTemplate = $listingProduct->getSynchronizationTemplate()->getChildObject();

            $typeQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyHasValueType();
            $minQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyHasValueMin();
            $maxQty = (int)$amazonSynchronizationTemplate->getRelistWhenQtyHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_LESS &&
                $productQty <= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_MORE &&
                $productQty >= $minQty) {
                $result = true;
            }

            if ($typeQty == Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_QTY_BETWEEN &&
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

    //------------------------------------

    public function inspectReviseQtyRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->_checkedQtyListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedQtyListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // Prepare actions params
        //--------------------
        $actionParams = array('only_data'=>array('qty'=>true,'variations'=>true));
        //--------------------

        // Amazon available status
        //--------------------
        if (!$listingProduct->isListed() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isRevisable()) {
            return false;
        }

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_REVISE,
                        array())
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
           return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
            return false;
        }
        if (!$listingProduct->getSynchronizationTemplate()->getChildObject()->isReviseWhenChangeQty()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $isVariationProduct = $listingProduct->getMagentoProduct()->isProductWithVariations() &&
                              (bool)count($listingProduct->getVariations());

        if (!$isVariationProduct) {

            $productQty = $listingProduct->getChildObject()->getQty();
            $channelQty = $listingProduct->getChildObject()->getOnlineQty();

            if ($productQty > 0 && $productQty != $channelQty) {
                $this->_runnerActions
                     ->setProduct(
                            $listingProduct,
                            Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_REVISE,
                            $actionParams
                     );
                return true;
            }

        } else {

            $totalQty = 0;
            $hasChange = false;

            $variations = $listingProduct->getVariations(true);

            foreach ($variations as $variation) {

                /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

                $productQty = $variation->getChildObject()->getQty();
                $channelQty = $variation->getChildObject()->getOnlineQty();

                if ($productQty != $channelQty) {
                    $hasChange = true;
                }

                $totalQty += $productQty;
            }

            if ($totalQty > 0 && $hasChange) {
                $this->_runnerActions
                     ->setProduct(
                            $listingProduct,
                            Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_REVISE,
                            $actionParams
                     );
                return true;
            }
        }
        //--------------------

        return false;
    }

    public function inspectRevisePriceRequirements(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // Is checked before?
        //--------------------
        if (in_array($listingProduct->getId(),$this->_checkedPriceListingsProductsIds)) {
            return false;
        } else {
            $this->_checkedPriceListingsProductsIds[] = $listingProduct->getId();
        }
        //--------------------

        // Prepare actions params
        //--------------------
        $actionParams = array('only_data'=>array('price'=>true,'variations'=>true));
        //--------------------

        // Amazon available status
        //--------------------
        if (!$listingProduct->isListed() || $listingProduct->isBlocked()) {
            return false;
        }

        if (!$listingProduct->isRevisable()) {
            return false;
        }

        if ($this->_runnerActions
                 ->isExistProductAction(
                        $listingProduct,
                        Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_REVISE,
                        array())
        ) {
            return false;
        }

        if ($listingProduct->isLockedObject(NULL) ||
            $listingProduct->isLockedObject('in_action')) {
           return false;
        }
        //--------------------

        // Correct synchronization
        //--------------------
        if (!$listingProduct->getListing()->isSynchronizationNowRun()) {
            return false;
        }
        if (!$listingProduct->getSynchronizationTemplate()->getChildObject()->isReviseWhenChangePrice()) {
            return false;
        }
        //--------------------

        // Check filters
        //--------------------
        $isVariationProduct = $listingProduct->getMagentoProduct()->isProductWithVariations() &&
                              (bool)count($listingProduct->getVariations());

        if (!$isVariationProduct) {

            $currentPrice = $listingProduct->getChildObject()->getPrice();
            $onlinePrice = $listingProduct->getChildObject()->getOnlinePrice();

            if ($currentPrice != $onlinePrice) {
                $this->_runnerActions
                     ->setProduct(
                            $listingProduct,
                            Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_REVISE,
                            $actionParams
                     );
                return true;
            }

        } else {

            $variations = $listingProduct->getVariations(true);

            foreach ($variations as $variation) {

                $currentPrice = $variation->getChildObject()->getPrice();
                $onlinePrice = $variation->getChildObject()->getOnlinePrice();

                if ($currentPrice != $onlinePrice) {
                    $this->_runnerActions
                         ->setProduct(
                                $listingProduct,
                                Ess_M2ePro_Model_Amazon_Connector_Product_Dispatcher::ACTION_REVISE,
                                $actionParams
                         );
                    return true;
                }
            }
        }
        //--------------------

        return false;
    }

    //####################################
}