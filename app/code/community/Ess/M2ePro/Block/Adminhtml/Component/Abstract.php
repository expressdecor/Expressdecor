<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Component_Abstract extends Mage_Adminhtml_Block_Widget_Container
{
    const TAB_ID_EBAY   = 'ebay';
    const TAB_ID_AMAZON = 'amazon';

    // ########################################

    protected $tabs = array();

    protected $enabledTab = NULL;

    protected $ebayTabBlock = NULL;

    protected $amazonTabBlock = NULL;

    protected $tabsContainerBlock = NULL;

    protected $tabsContainerId = 'components_container';

    protected $useAjax = false;

    // ########################################

    /**
     * @return Ess_M2ePro_Helper_Component
     */
    protected function getComponentHelper()
    {
        return Mage::helper('M2ePro/Component');
    }

    // ########################################

    public function setEnabledTab($id)
    {
        $this->enabledTab = $id;
    }

    public function enableEbayTab()
    {
        $this->setEnabledTab(self::TAB_ID_EBAY);
    }

    public function enableAmazonTab()
    {
        $this->setEnabledTab(self::TAB_ID_AMAZON);
    }

    // ----------------------------------------

    protected function isTabEnabled($id)
    {
        if (is_null($this->enabledTab)) {
            return true;
        }

        return $id == $this->enabledTab;
    }

    // ----------------------------------------

    protected function canUseAjax()
    {
        if (count($this->tabs) < 2) {
            return false;
        }

        return $this->useAjax;
    }

    // ########################################

    protected function _prepareLayout()
    {
        if (count($this->getComponentHelper()->getActiveComponents()) == 0) {
            throw new LogicException('At least 1 channel should be enabled.');
        }

        parent::_prepareLayout();
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->initializeEbay();
        $this->initializeAmazon();

        return parent::_beforeToHtml();
    }

    protected function initializeEbay()
    {
        if (Mage::helper('M2ePro/Component_Ebay')->isActive()) {
            $this->initializeTab(self::TAB_ID_EBAY);
        }
    }

    protected function initializeAmazon()
    {
        if (Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            $this->initializeTab(self::TAB_ID_AMAZON);
        }
    }

    protected function initializeTab($id)
    {
        if ($this->isTabEnabled($id)) {
            $this->tabs[] = $id;
        }
    }

    // ########################################

    protected function _toHtml()
    {
        return parent::_toHtml() . $this->_componentsToHtml();
    }

    protected function _componentsToHtml()
    {
        $tabsCount = count($this->tabs);

        if ($tabsCount <= 0) {
            return '';
        }

        if ($tabsCount == 1) {
            $tabId = reset($this->tabs);

            return $this->getTabHtmlById($tabId);
        }

        $tabsContainer = $this->getTabsContainerBlock();
        $tabsContainer->setDestElementId($this->tabsContainerId);

        foreach ($this->tabs as $tabId) {
            $tab = $this->prepareTabById($tabId);
            $tabsContainer->addTab($tabId, $tab);
        }

        $tabsContainer->setActiveTab($this->getActiveTab());

        return $tabsContainer->toHtml() . $this->getTabsContainerDestinationHtml();
    }

    // ########################################

    protected function prepareTabById($id)
    {
        $label = $this->getTabLabelById($id);

        $tab = array(
            'label' => $label,
            'title' => $label
        );

        if ($this->canUseAjax() && $this->getActiveTab() != $id) {
            $tab['class'] = 'ajax';
            $tab['url'] = $this->getTabUrlById($id);
        } else {
            $tab['content'] = $this->getTabHtmlById($id);
        }

        return $tab;
    }

    protected function getTabBlockById($id)
    {
        if ($id == self::TAB_ID_EBAY) {
            return $this->getEbayTabBlock();
        }

        if ($id == self::TAB_ID_AMAZON) {
            return $this->getAmazonTabBlock();
        }

        return NULL;
    }

    protected function getTabHtmlById($id)
    {
        if ($id == self::TAB_ID_EBAY) {
            return $this->getEbayTabHtml();
        }

        if ($id == self::TAB_ID_AMAZON) {
            return $this->getAmazonTabHtml();
        }

        return '';
    }

    protected function getTabLabelById($id)
    {
        if ($id == self::TAB_ID_EBAY) {
            return Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
        }

        if ($id == self::TAB_ID_AMAZON) {
            return Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);
        }

        return 'N/A';
    }

    protected function getTabUrlById($id)
    {
        if ($id == self::TAB_ID_EBAY) {
            return $this->getEbayTabUrl();
        }

        if ($id == self::TAB_ID_AMAZON) {
            return $this->getAmazonTabUrl();
        }

        return '';
    }

    // ########################################

    protected function setSingleBlock(Mage_Core_Block_Abstract $block)
    {
        if (count($this->tabs) == 1) {
            $tabId = reset($this->tabs);

            $tabId == self::TAB_ID_EBAY   && $this->ebayTabBlock = $block;
            $tabId == self::TAB_ID_AMAZON && $this->amazonTabBlock = $block;
        }

        return $this;
    }

    protected function getSingleBlock()
    {
        if (count($this->tabs) != 1) {
            return NULL;
        }

        $tabId = reset($this->tabs);

        return $this->getTabBlockById($tabId);
    }

    // ########################################

    /**
     * @abstract
     * @return Mage_Core_Block_Abstract
     */
    abstract protected function getEbayTabBlock();

    public function getEbayTabHtml()
    {
        return $this->getEbayTabBlock()->toHtml();
    }

    protected function getEbayTabUrl()
    {
        return '';
    }

    // ########################################

    /**
     * @abstract
     * @return Mage_Core_Block_Abstract
     */
    abstract protected function getAmazonTabBlock();

    public function getAmazonTabHtml()
    {
        return $this->getAmazonTabBlock()->toHtml();
    }

    protected function getAmazonTabUrl()
    {
        return '';
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Block_Adminhtml_Component_Tabs
     */
    protected function getTabsContainerBlock()
    {
        if (is_null($this->tabsContainerBlock)) {
            $this->tabsContainerBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_component_tabs');
        }

        return $this->tabsContainerBlock;
    }

    protected function getTabsContainerDestinationHtml()
    {
        return '<div id="'.$this->tabsContainerId.'"></div>';
    }

    // ########################################

    protected function getActiveTab()
    {
        $activeTab = $this->getRequest()->getParam('tab');
        if (is_null($activeTab)) {
            Mage::helper('M2ePro/Component_Ebay')->isDefault()   && $activeTab = self::TAB_ID_EBAY;
            Mage::helper('M2ePro/Component_Amazon')->isDefault() && $activeTab = self::TAB_ID_AMAZON;
        }

        return $activeTab;
    }

    // ########################################
}