<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing extends Ess_M2ePro_Block_Adminhtml_Component_Tabs_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Listings');
        //------------------------------

        if (!is_null($this->getRequest()->getParam('back'))) {

            $tempUrl = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listing/index');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.$tempUrl.'\')',
                'class'     => 'back'
            ));
        }

        $tempUrl = $this->getUrl(
            '*/adminhtml_log/listing',
            array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index'))
        );
        $this->_addButton('general_log', array(
            'label'     => Mage::helper('M2ePro')->__('General Log'),
            'onclick'   => 'setLocation(\'' . $tempUrl .'\')',
            'class'     => 'button_link'
        ));

        $tempUrl = $this->getUrl(
            '*/adminhtml_listing/search',
            array('back' => Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_listing/index'))
        );
        $this->_addButton('search_products', array(
            'label'     => Mage::helper('M2ePro')->__('Search Products'),
            'onclick'   => 'setLocation(\'' . $tempUrl . '\')',
            'class'     => 'button_link search'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->useAjax = true;
    }

    // ########################################

    protected function getTabsContainerBlock()
    {
        return parent::getTabsContainerBlock()->setId('listing');
    }

    // ########################################

    protected function getEbayTabBlock()
    {
        if (is_null($this->ebayTabBlock)) {
            $this->ebayTabBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing');
        }
        return $this->ebayTabBlock;
    }

    public function getEbayTabHtml()
    {
        $javascript = '';

        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascript = $this->getEbayTabBlock()->getTemplatesButtonJavascript();
        }

        return $javascript .
               $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_filter')->toHtml() .
               parent::getEbayTabHtml();
    }

    public function getEbayTabUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/index');
    }

    // ########################################

    protected function getAmazonTabBlock()
    {
        $tutorialShowed = Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/cache/', 'amazon_listing_tutorial_showed');

        if (!$tutorialShowed) {
            $this->amazonTabBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_tutorial');
            return $this->amazonTabBlock;
        }

        if (is_null($this->amazonTabBlock)) {
            $this->amazonTabBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing');
        }
        return $this->amazonTabBlock;
    }

    public function getAmazonTabHtml()
    {
        $tutorialShowed = Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/cache/', 'amazon_listing_tutorial_showed');

        if (!$tutorialShowed) {
            return parent::getAmazonTabHtml();
        }

        $javascript = '';

        if ($this->getRequest()->isXmlHttpRequest()) {
            $javascript = $this->getAmazonTabBlock()->getTemplatesButtonJavascript();
        }

        return $javascript .
               $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_filter')->toHtml() .
               parent::getAmazonTabHtml();
    }

    public function getAmazonTabUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_listing/index');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if (count($this->getComponentHelper()->getActiveComponents()) == 1) {
            $grid = $this->getSingleBlock()->getChild('grid');
            $grid && $this->setSingleBlock($grid);
        }
    }

    protected function _componentsToHtml()
    {
        $javascript = <<<JAVASCRIPT

<script type="text/javascript">

if (varienGlobalEvents) {
    varienGlobalEvents.attachEventHandler('showTab', function() {
        if (typeof listingJsTabs == 'undefined') {
            return;
        }

        // we need to remove container if it is already exist to be sure
        // it has the last element with class content-header in DOM (see tools.js createToolbar())
        // ----------
        if ($('fake_buttons_container')) {
            $('fake_buttons_container').remove();
        }
        // ----------

        // prepare fake buttons container
        // ----------
        var fakeButtonsContainer = new Element('div', {
            id: 'fake_buttons_container'
        });

        document.body.insertBefore(fakeButtonsContainer, document.body.lastChild);

        fakeButtonsContainer.hide();
        // ----------

        // update fake buttons container html and reset floating toolbar
        // ----------
        var activeTabButtonsHtml = $$('#' + listingJsTabs.activeTab.id + '_content div.content-header')[0].innerHTML;
        $('fake_buttons_container').update('<div class="content-header">' + activeTabButtonsHtml + '</div>');

        updateTopButtonToolbarToggle();
        // ----------
    });
}

</script>

JAVASCRIPT;

        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_help');

        return $helpBlock->toHtml() . parent::_componentsToHtml() . $javascript;
    }

    // ########################################

    public function getButtonsHtml($area = null)
    {
        $helper = $this->getComponentHelper();
        $javascript = $this->getButtonsJavascript();

        if (count($helper->getActiveComponents()) != 1) {
            return $javascript . parent::getButtonsHtml($area);
        }

        $parentBlock = $this->getSingleBlock()->getParentBlock();
        $buttonsHtml = $parentBlock ? $parentBlock->getButtonsHtml() : '';

        return $javascript . $buttonsHtml;
    }

    private function getButtonsJavascript()
    {
        if (count($this->tabs) <= 0) {
            return '';
        }

        if (count($this->tabs) == 1) {
            $parentBlock = $this->getSingleBlock()->getParentBlock();

            return $parentBlock ? $parentBlock->getTemplatesButtonJavascript() : '';
        }

        $javascript = '';
        $javascript .= $this->getEbayButtonsJavascript();
        $javascript .= $this->getAmazonButtonsJavascript();

        return $javascript;
    }

    private function getEbayButtonsJavascript()
    {
        if (!Mage::helper('M2ePro/Component_Ebay')->isActive()) {
            return '';
        }

        if ($this->getActiveTab() != self::TAB_ID_EBAY) {
            return '';
        }

        return $this->getEbayTabBlock()->getTemplatesButtonJavascript();
    }

    private function getAmazonButtonsJavascript()
    {
        if (!Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            return '';
        }

        if ($this->getActiveTab() != self::TAB_ID_AMAZON) {
            return '';
        }

        return $this->getAmazonTabBlock()->getTemplatesButtonJavascript();
    }

    // ########################################
}