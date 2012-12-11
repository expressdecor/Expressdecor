<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Update
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 50;
    const PERCENTS_INTERVAL = 50;

    const LOCK_ITEM_PREFIX = 'synchronization_ebay_other_listings';

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
        $this->_profiler->addTitle($componentName.'Update');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Update 3rd Party Listings" action is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Update 3rd Party Listings" action is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Get and process items from eBay');

        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountsCollection->addFieldToFilter('other_listings_synchronization',
                                              Ess_M2ePro_Model_Ebay_Account::OTHER_LISTINGS_SYNCHRONIZATION_YES);

        $accountsTotalCount = $accountsCollection->getSize();

        $accountIteration = 1;
        $percentsForAccount = self::PERCENTS_INTERVAL;

        if ($accountsTotalCount > 0) {
            $percentsForAccount = self::PERCENTS_INTERVAL/(int)$accountsCollection->getSize();
        }

        foreach ($accountsCollection->getItems() as $accountObj) {

            /** @var $accountObj Ess_M2ePro_Model_Account */

            if (!$this->isLockedAccount($accountObj->getId())) {
                $this->processAccount($accountObj,$percentsForAccount);
            }

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    private function processAccount(Ess_M2ePro_Model_Account $account, $percentsForAccount)
    {
        $this->_profiler->addTitle('Starting account "'.$account->getData('title').'"');

        // ->__('Task "3rd Party Listings Synchronization" for eBay account: "%acc%" is started. Please wait...')
        $status = 'Task "Update 3rd Party Listings" for eBay account: "%acc%" is started. Please wait...';
        $tempString = str_replace('%acc%',$account->getData('title'),Mage::helper('M2ePro')->__($status));
        $this->_lockItem->setStatus($tempString);

        $sinceTime = $account->getData('other_listings_last_synchronization');

        if (is_null($sinceTime) || empty($sinceTime)) {

            $marketplaceCollection = Mage::getModel('M2ePro/Marketplace')->getCollection();
            $marketplaceCollection->addFieldToFilter('status',Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
            $marketplace = $marketplaceCollection->getFirstItem();

            if (!$marketplace->getId()) {
                $marketplace = Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_US;
            }

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher');
            $dispatcherObject->processConnector('otherListings', 'update' ,'requester',
                                                array(), $marketplace, $account, NULL,
                                                'Ess_M2ePro_Model_Ebay_Synchronization_Tasks');
            return;
        }

        $tempSinceTime = $this->prepareSinceTime($sinceTime);

        $responseData = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                            ->processVirtualAbstract('item','get','changes',
                                                     array('since_time'=>$tempSinceTime),NULL,
                                                     NULL,$account->getId(),NULL);

        if (!isset($responseData['items']) || !isset($responseData['to_time'])) {

            $tempSinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $tempSinceTime->modify("-1 day");
            $tempSinceTime = $tempSinceTime->format('Y-m-d H:i:s');

            $responseData = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                            ->processVirtualAbstract('item','get','changes',
                                                     array('since_time'=>$tempSinceTime),NULL,
                                                     NULL,$account->getId(),NULL);

            if (!isset($responseData['items']) || !isset($responseData['to_time'])) {

                $tempSinceTime = new DateTime('now', new DateTimeZone('UTC'));
                $tempSinceTime = $tempSinceTime->format('Y-m-d H:i:s');

                $responseData = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                            ->processVirtualAbstract('item','get','changes',
                                                     array('since_time'=>$tempSinceTime),NULL,
                                                     NULL,$account->getId(),NULL);
            }
        }

        $status = <<<STATUS
Task "Update 3rd Party Listings" for eBay account: "%acc%" is in data processing state. Please wait...
STATUS;
        $tempString = str_replace('%acc%', $account->getData('title'), Mage::helper('M2ePro')->__($status));
        $this->_lockItem->setStatus($tempString);

        /** @var $updatingModel Ess_M2ePro_Model_Ebay_Listing_Other_Updating */
        $updatingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Updating');
        $updatingModel->initialize($account);
        $updatingModel->processResponseData($responseData);

        $this->_profiler->addEol();
    }

    //####################################

    private function prepareSinceTime($lastSinceTime)
    {
        // Get last since time
        //------------------------
        if (is_null($lastSinceTime) || empty($lastSinceTime)) {
            $lastSinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $lastSinceTime->modify("-1 year");
        } else {
            $lastSinceTime = new DateTime($lastSinceTime, new DateTimeZone('UTC'));
        }
        //------------------------

        // Get min shold for synch
        //------------------------
        $minSholdTime = new DateTime('now', new DateTimeZone('UTC'));
        $minSholdTime->modify("-1 month");
        //------------------------

        // Prepare last since time
        //------------------------
        if ((int)$lastSinceTime->format('U') < (int)$minSholdTime->format('U')) {
            $lastSinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $lastSinceTime->modify("-10 days");
        }
        //------------------------

        return strftime("%Y-%m-%d %H:%M:%S", (int)$lastSinceTime->format('U'));
    }

    private function isLockedAccount($accountId)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$accountId);

        $tempGroup = '/ebay/synchronization/settings/other_listings/update/';
        $maxDeactivateTime = (int)Mage::helper('M2ePro/Module')->getConfig()
                                    ->getGroupValue($tempGroup,'max_deactivate_time');
        $lockItem->setMaxDeactivateTime($maxDeactivateTime);

        if ($lockItem->isExist()) {
            return true;
        }

        return false;
    }

    //####################################
}