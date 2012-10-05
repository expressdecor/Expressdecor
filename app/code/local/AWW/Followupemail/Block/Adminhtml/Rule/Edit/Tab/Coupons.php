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


class AWW_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Coupons extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $data = Mage::registry('followupemail_data');

        if (is_object($data)) $data = $data->getData();

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('coupons', array('legend' => $this->__('Coupons')));

        $fieldset->addField('coupon_enabled', 'select', array(
            'label'  => $this->__('Enable coupons for this rule'),
            'name'   => 'coupon_enabled',
            'values' => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'onchange' => 'checkCouponEnabled()'
        ));

        $fieldset->addField('coupon_sales_rule_id', 'select', array(
            'label' => $this->__('Shopping Cart Price Rule'),
            'name' => 'coupon_sales_rule_id',
            'values' => Mage::getSingleton('followupemail/salesrule_rule')->toOptionArray()
        ));

        $fieldset->addField('coupon_prefix', 'text', array(
            'label' => $this->__('Coupon Code Prefix'),
            'name' => 'coupon_prefix'
        ));

        $fieldset->addField('coupon_expire_days', 'text', array(
            'label' => $this->__('Coupon expires after, days'),
            'name' => 'coupon_expire_days'
        ));

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
