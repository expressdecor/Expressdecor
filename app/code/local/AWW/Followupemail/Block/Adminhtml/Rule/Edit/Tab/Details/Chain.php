<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Followupemail
 * @version    3.4.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AWW_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Details_Chain extends Mage_Adminhtml_Block_Widget implements Varien_Data_Form_Element_Renderer_Interface 
{

    public function __construct() {
        $this->setTemplate('followupemail/rule/edit/details/chain.phtml');
    }

    public function isMultiWebsites() {
        return !Mage::app()->isSingleStoreMode();
    }

    public function getEmailTemplates() 
    {
        $result = array(0 => $this->__('--- Select Template ---'));
        $result = array_merge($result, Mage::getModel('followupemail/source_rule_template')->getEmailTemplates());
        return $result;
    }

    public function getValues() {
        $__data = $this->getElement()->getValue();
        if (!is_array($__data)) $__data = array();
        return $__data;
    }

    protected function _prepareLayout() {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $this->__('Add email'),
                    'onclick'   => 'emailsControl.addItem()',
                    'class' => 'add'
                )
            )
        );
      return parent::_prepareLayout();
    }

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function getAddButtonHtml() {
        return $this->getChildHtml('add_button');
    }
}