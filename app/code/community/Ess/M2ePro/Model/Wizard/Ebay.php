<?php

/*
* @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_Wizard_Ebay extends Ess_M2ePro_Model_Wizard
{
    // ########################################

    protected $steps = array(
        'marketplace',
        'synchronization',
        'otherListing',
        'account'
    );

    // ########################################

    public function isActive()
    {
        return Mage::helper('M2ePro/Component_Ebay')->isActive();
    }

    // ########################################

    public function getTitle()
    {
        return Ess_M2ePro_Helper_Component_Ebay::TITLE;
    }

    // ########################################
}