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
 * @package    AWW_Followupemail
 * @version    3.4.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AWW_Followupemail_Block_Adminhtml_Rule_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() 
    {
        $data = Mage::registry('followupemail_data');

        if (is_object($data)) $data = $data->getData();

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('general', array('legend' => $this->__('Rule')));

        # title field
        $fieldset->addField('title', 'text', array(
                'label'    => $this->__('Title'),
                'name'     => 'title',
                'required' => true,
            ));

        # is_active field
        $fieldset->addField('is_active', 'select', array(
                'label'  => $this->__('Status'),
                'name'   => 'is_active',
                'values' => AWW_Followupemail_Model_Source_Rule_Status::toOptionArray(),
            ));

        # event_type field
        $fieldset->addField('event_type', 'select', array(
                'label'  => $this->__('Event'),
                'name'   => 'event_type',
                'values' => AWW_Followupemail_Model_Source_Rule_Types::toOptionArray(),
                'required' => true,
                'onchange' => 'checkEventType()',
            ));

        # cancel_events field
        $fieldset->addField('cancel_events', 'multiselect', array(
                'label'  => $this->__('Cancellation events'),
                'name'   => 'cancel_events[]',
                'values' => AWW_Followupemail_Model_Source_Rule_Types::toOptionArray(TRUE),
                'note'   => $this->__('Once selected event(s) happen they cancel email sending for the object'),
            ));

        # customer_groups field
        $fieldset->addField('customer_groups', 'multiselect', array(
            'name'      => 'customer_groups[]',
            'label'     => $this->__('Customer groups'),
            'title'     => $this->__('Customer groups'),
            'required'  => true,
            'values'    => AWW_Followupemail_Model_Source_Customer_Group::toOptionArray(),
        ));

        # sku field
        $fieldset->addField('sku', 'text', array(
                'label'    => $this->__('SKU'),
                'name'     => 'sku',
                'note'   => $this->__('Separate multiple SKUs by commas'),
            ));

        # sale_amount_value field
        $fieldset->addField('sale_amount_value', 'select', array(
                'label'  => $this->__('Sale amount'),
                'name'   => 'sale_amount_value',
                'value' => $data['sale_amount_value'],
                'condition' => $data['sale_amount_condition'],
                'conditions' => Mage::getModel('followupemail/source_rule_saleamount')->toOptionArray(true),
            ));
        $form->getElement('sale_amount_value')->setRenderer($this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_details_saleamount'));

        # chain field
        $fieldset->addField('chain', 'text', array(
                'label' => $this->__('Email chain'),
                'name'  => 'chain',
                'required' => true,
                'class' => 'requried-entry'
            ));
        $form->getElement('chain')->setRenderer($this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_details_chain'));

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}