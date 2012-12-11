<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Marketplace extends Ess_M2ePro_Block_Adminhtml_Component_Tabs_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('marketplace');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_marketplace';
        //------------------------------

        // Form id of marketplace_general_form
        //------------------------------
        $this->tabsContainerId = 'edit_form';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Marketplaces');
        //------------------------------

        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');

        $activeWizard = $wizardHelper->getActiveUpgrade();

        if ($activeWizard &&
            $wizardHelper->getStep($wizardHelper->getNick($activeWizard)) == 'marketplace') {

            $this->setEnabledTab($wizardHelper->getNick($activeWizard));

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'MarketplaceHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $this->_addButton('close', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Complete This Step'),
                'onclick'   => 'MarketplaceHandlerObj.completeStep();',
                'class'     => 'close'
            ));

        } else {

            if (Mage::helper('M2ePro/Component_Ebay')->isActive()) {
                $this->_addButton('goto_general_templates', array(
                    'label'     => Mage::helper('M2ePro')->__('General Templates'),
                    'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_template_general/index').'\')',
                    'class'     => 'button_link'
                ));
            }

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'MarketplaceHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $this->_addButton('run_synch_now', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Update'),
                'onclick'   => 'MarketplaceHandlerObj.saveSettings(\'runSynchNow\');',
                'class'     => 'save save_and_update_marketplaces'
            ));

        }
    }

    // ########################################

    protected function getEbayTabBlock()
    {
        if (is_null($this->ebayTabBlock)) {
            $this->ebayTabBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_marketplace_form');
        }
        return $this->ebayTabBlock;
    }

    protected function getAmazonTabBlock()
    {
        if (is_null($this->amazonTabBlock)) {
            $this->amazonTabBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_marketplace_form');
        }
        return $this->amazonTabBlock;
    }

    // ########################################

    protected function _toHtml()
    {
        return '<div id="marketplaces_progress_bar"></div>' .
               '<div id="marketplaces_content_container">' .
               parent::_toHtml() .
               '</div>';
    }

    protected function _componentsToHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_help');

        $formBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_general_form');
        count($this->tabs) == 1 && $formBlock->setChildBlockId($this->getSingleBlock()->getContainerId());

        return $helpBlock->toHtml() .
               parent::_componentsToHtml() .
               $formBlock->toHtml();
    }

    protected function getTabsContainerDestinationHtml()
    {
        return '';
    }

    // ########################################
}