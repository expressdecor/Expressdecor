<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Synchronization extends Ess_M2ePro_Block_Adminhtml_Component_Tabs_Container
{
    const TAB_ID_GENERAL = 'general';

    protected $generalTabBlock = NULL;

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('synchronization');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_synchronization';
        //------------------------------

        // Form id of marketplace_general_form
        //------------------------------
        $this->tabsContainerId = 'edit_form';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Synchronization');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');
        /* @var $upgrader Ess_M2ePro_Model_Wizard */
        $upgrader = $wizardHelper->getActiveUpgrade();

        if (!$upgrader || $wizardHelper->getStep($wizardHelper->getNick($upgrader)) != 'synchronization') {

            $this->_addButton('goto_accounts', array(
                'label'     => Mage::helper('M2ePro')->__('Accounts'),
                'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_account/index').'\')',
                'class'     => 'button_link'
            ));

            $tempUrl = $this->getUrl(
                '*/adminhtml_log/synchronization',
                array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_synchronization/index'))
            );
            $this->_addButton('view_log', array(
                'label'     => Mage::helper('M2ePro')->__('View Log'),
                'onclick'   => 'setLocation(\'' . $tempUrl . '\')',
                'class'     => 'button_link'
            ));

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'SynchronizationHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $tempParams = Mage::helper('M2ePro')->escapeHtml(json_encode(Mage::helper('M2ePro/Component')
                                                ->getActiveComponents()));
            $this->_addButton('run_all_enabled_now', array(
                'label'     => Mage::helper('M2ePro')->__('Run Enabled Now'),
                'onclick'   => 'SynchronizationHandlerObj.saveSettings(\'runAllEnabledNow\', ' . $tempParams . ');',
                'class'     => 'save'
            ));

            $this->_addButton('save', array(
                'label'     => Mage::helper('M2ePro')->__('Save Settings'),
                'onclick'   => 'SynchronizationHandlerObj.saveSettings(\'\', ' . $tempParams . ')',
                'class'     => 'save'
            ));

        } else {

            $this->setEnabledTab($wizardHelper->getNick($upgrader));

            $this->_addButton('save', array(
                'label'     => Mage::helper('M2ePro')->__('Save Settings'),
                'onclick'   => 'SynchronizationHandlerObj.saveSettings(\'\',' . $wizardHelper->getNick($upgrader) . ')',
                'class'     => 'save'
            ));

            $this->_addButton('close', array(
                'label'     => Mage::helper('M2ePro')->__('Complete This Step'),
                'onclick'   => 'SynchronizationHandlerObj.completeStep();',
                'class'     => 'close'
            ));
        }
        //------------------------------
    }

    public function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        SynchProgressBarObj = new ProgressBar('synchronization_progress_bar');
        SynchWrapperObj = new AreaWrapper('synchronization_content_container');
    });

</script>
JAVASCRIPT;

        return $javascriptsMain .
               '<div id="synchronization_progress_bar"></div>' .
               '<div id="synchronization_content_container">' .
               parent::_toHtml() .
               '</div>';
    }

    // ########################################

    protected function getEbayTabBlock()
    {
        if (is_null($this->ebayTabBlock)) {
            $this->ebayTabBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_synchronization_form');
        }
        return $this->ebayTabBlock;
    }

    public function getEbayTabHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_synchronization_help');

        return $helpBlock->toHtml() . parent::getEbayTabHtml();
    }

    // ########################################

    protected function getAmazonTabBlock()
    {
        if (is_null($this->amazonTabBlock)) {
            $this->amazonTabBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_synchronization_form');
        }
        return $this->amazonTabBlock;
    }

    public function getAmazonTabHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_synchronization_help');

        return $helpBlock->toHtml() . parent::getAmazonTabHtml();
    }

    // ########################################

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function getGeneralTabBlock()
    {
        if (is_null($this->generalTabBlock)) {
            $this->generalTabBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_synchronization_general');
        }

        return $this->generalTabBlock;
    }

    public function getGeneralTabHtml()
    {
        return $this->getGeneralTabBlock()->toHtml();
    }

    // ########################################

    protected function getTabBlockById($id)
    {
        if ($id == self::TAB_ID_GENERAL) {
            return $this->getGeneralTabBlock();
        }

        return parent::getTabBlockById($id);
    }

    protected function getTabHtmlById($id)
    {
        if ($id == self::TAB_ID_GENERAL) {
            return $this->getGeneralTabHtml();
        }

        return parent::getTabHtmlById($id);
    }

    protected function getTabLabelById($id)
    {
        if ($id == self::TAB_ID_GENERAL) {
            return Mage::helper('M2ePro')->__('Global');
        }

        return parent::getTabLabelById($id);
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->initializeTab(self::TAB_ID_GENERAL);

        parent::_beforeToHtml();
    }

    protected function _componentsToHtml()
    {
        $formBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_synchronization_form');
        count($this->tabs) == 1 && $formBlock->setChildBlockId($this->getSingleBlock()->getContainerId());

        return parent::_componentsToHtml() . $formBlock->toHtml();
    }

    protected function getTabsContainerDestinationHtml()
    {
        return '';
    }

    // ########################################
}