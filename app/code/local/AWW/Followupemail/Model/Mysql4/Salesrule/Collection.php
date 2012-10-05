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


class AWW_Followupemail_Model_Mysql4_Salesrule_Collection extends Mage_SalesRule_Model_Mysql4_Rule_Collection {
    public function setValidationFilter($websiteId, $customerGroupId, $couponCode='', $now=null) {
        $__isMagentoVersion = Mage::helper('followupemail')->checkVersion('1.6');
        if(!$couponCode || !Mage::helper('followupemail/coupon')->isFUECoupon($couponCode) || $__isMagentoVersion) {
            return parent::setValidationFilter($websiteId, $customerGroupId, $couponCode, $now);
        }
        if (is_null($now)) {
            $now = Mage::getModel('core/date')->date('Y-m-d');
        }

        $this->getSelect()->where('is_active=1');
        $this->getSelect()->where('find_in_set(?, website_ids)', (int)$websiteId);
        $this->getSelect()->where('find_in_set(?, customer_group_ids)', (int)$customerGroupId);

        if ($couponCode) {
            $this->getSelect()->joinLeft(
                array('extra_coupon' => $this->getTable('salesrule/coupon')),
                'extra_coupon.rule_id = main_table.rule_id AND extra_coupon.is_primary IS NULL AND extra_coupon.code = '.$this->getConnection()->quote($couponCode),
                array()
            );
            $this->getSelect()->group('main_table.rule_id');
            $this->getSelect()->where(''
                . $this->getSelect()->getAdapter()->quoteInto(' main_table.coupon_type <> ?', Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
                . $this->getSelect()->getAdapter()->quoteInto(' OR primary_coupon.code = ?', $couponCode)
            );
        } else {
            $this->getSelect()->where('main_table.coupon_type = ?', Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON);
        }
        $this->getSelect()->where('from_date is null or from_date<=?', $now);
        $this->getSelect()->where('to_date is null or to_date>=?', $now);
        $this->getSelect()->order('sort_order');
        $this->getSelect()->where('extra_coupon.code = ?', $couponCode);
        return $this;
    }
}
