<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_Synchronization_Edit_Tabs_Source extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingOtherSynchronizationEditTabsSource');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/other/synchronization/tabs/source.phtml');
    }

    // ####################################

    protected function _beforeToHtml()
    {
        $this->attributes = Mage::helper('M2ePro/Magento')->getAttributesByAllAttributeSets();

        return parent::_beforeToHtml();
    }

    // ####################################
}