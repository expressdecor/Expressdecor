<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Component_Grid_Container extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    abstract protected function getEbayNewUrl();

    abstract protected function getAmazonNewUrl();

    // ########################################

    protected function getAddButtonOnClickAction()
    {
        /** @var $helper Ess_M2ePro_Helper_Component */
        $helper = Mage::helper('M2ePro/Component');
        $action = '';

        if (count($helper->getActiveComponents()) == 1) {
            $url = Mage::helper('M2ePro/Component_Ebay')->isActive()
                ? $this->getEbayNewUrl() : $this->getAmazonNewUrl();
            $action = 'setLocation(\''.$url.'\');';
        }

        return $action;
    }

    // ########################################

    public function _toHtml()
    {
        return $this->getAddButtonJavascript() . parent::_toHtml();
    }

    // ----------------------------------------

    protected function getAddButtonJavascript()
    {
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) < 2) {
            return '';
        }

        $tempDropDownHtml = Mage::helper('M2ePro')->escapeJs($this->getAddButtonDropDownHtml());

        return <<<JAVASCRIPT
<script type="text/javascript">

    Event.observe(window, 'load', function() {
        $$('.add-button-drop-down')[0].innerHTML += '{$tempDropDownHtml}';
        DropDownObj = new DropDown();
        DropDownObj.prepare($$('.add-button-drop-down')[0]);
    });

</script>
JAVASCRIPT;
    }

    protected function getAddButtonDropDownHtml()
    {
        $ebay = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
        $amazon = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);

        return <<<HTML
<ul style="display: none;">
    <li href="{$this->getEbayNewUrl()}">{$ebay}</li>
    <li href="{$this->getAmazonNewUrl()}">{$amazon}</li>
</ul>
HTML;
    }

    // ########################################
}