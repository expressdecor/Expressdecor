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


class AWW_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Sendtest extends Mage_Adminhtml_Block_Widget_Form 
{
    protected function _prepareForm()
    {
        $data = Mage::registry('followupemail_data');
        if (is_object($data)) $data = $data->getData();

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('followupemail_sendtest', array('legend'=>$this->__('Send test email to')));

        $fieldset->addField('test_recipient', 'text', array(
            'name'  => 'test_recipient',
            'label' => $this->__('Test recipient'),
            'title' => $this->__('Test recipient'),
        ));

        $form->setValues($data);


        $fieldset = $form->addFieldset('followupemail_sendtest_objects', array('legend'=>$this->__('Test object IDs')));

        $fieldset->addField('test_customer_id', 'text', array(
            'name'  => 'test[customer_id]',
            'label' => $this->__('Customer ID'),
            'title' => $this->__('Customer ID'),
            'note'  => $this->__('The ID of the customer'),
            'value' => isset($data['test']['customer_id']) ? $data['test']['customer_id'] : '',
        ));

        $fieldset->addField('test_customer_email', 'text', array(
            'name'  => 'test[customer_email]',
            'label' => $this->__('Customer email'),
            'title' => $this->__('Customer email'),
            'note'  => $this->__('The email of the customer'),
            'value' => isset($data['test']['customer_email']) ? $data['test']['customer_email'] : '',
        ));

        $fieldset->addField('order_increment_id', 'text', array(
            'name'  => 'test[order_increment_id]',
            'label' => $this->__('Order #'),
            'title' => $this->__('Order #'),
            'note'  => $this->__('The Increment ID of the order'),
            'value' => isset($data['test']['order_increment_id']) ? $data['test']['order_increment_id'] : '',
        ));

        $fieldset->addField('test_quote_id', 'text', array(
            'name'  => 'test[quote_id]',
            'label' => $this->__('Cart ID'),
            'title' => $this->__('Cart ID'),
            'note'  => $this->__('The ID of the cart'),
            'value' => isset($data['test']['quote_id']) ? $data['test']['quote_id'] : '',
        ));

        $fieldset->addField('test_wishlist_id', 'text', array(
            'name'  => 'test[wishlist_id]',
            'label' => $this->__('Wishlist ID'),
            'title' => $this->__('Wishlist ID'),
            'note'  => $this->__('The ID of the wishlist'),
            'value' => isset($data['test']['wishlist_id']) ? $data['test']['wishlist_id'] : '',
        ));

        $fieldset->addField('test_product_id', 'text', array(
            'name'  => 'test[product_id]',
            'label' => $this->__('Product ID'),
            'title' => $this->__('Product ID'),
            'note'  => $this->__('The ID of the product'),
            'value' => isset($data['test']['product_id']) ? $data['test']['product_id'] : '',
        ));

        $fieldset->addField('test_resume_code', 'text', array(
            'name'  => 'test[resume_code]',
            'label' => $this->__('Resume code'),
            'title' => $this->__('Resume code'),
            'note'  => $this->__('The resume code is used to authorize the customer when he/she comes back by the link sent in email'),
            'value' => isset($data['test']['resume_code']) ? $data['test']['resume_code'] : '',
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }
}