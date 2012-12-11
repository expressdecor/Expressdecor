<?php

/*
* @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_EbayOtherListing extends Ess_M2ePro_Model_Wizard
{
    const TITLE = 'eBay 3rd Party Listing';

    // ########################################

    protected $steps = array(
        'synchronization',
        'account',
        'reset'
    );

    // ########################################

    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Ebay')->isActive();
    }

    // ########################################

    public function getTitle()
    {
        return self::TITLE;
    }

    // ########################################
}