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


class AWW_Followupemail_Model_Source_Rule_Types
{
    const RULE_TYPE_ORDER_STATUS_PREFIX         = 'order_status_';
    const RULE_TYPE_CUSTOMER_NEW                = 'customer_new';
    const RULE_TYPE_CUSTOMER_LOGGED_IN          = 'customer_logged_in';
    const RULE_TYPE_CUSTOMER_LAST_ACTIVITY      = 'customer_last_activity';
    const RULE_TYPE_CUSTOMER_BIRTHDAY           = 'customer_birthday';
    const RULE_TYPE_CUSTOMER_CAME_BACK_BY_LINK  = 'customer_came_back_by_link';
    const RULE_TYPE_WISHLIST_SHARED             = 'wishlist_shared';
    const RULE_TYPE_WISHLIST_PRODUCT_ADD        = 'wishlist_add_product';
    const RULE_TYPE_ABANDONED_CART_NEW          = 'cart_abandoned_new';
    const RULE_TYPE_CUSTOMER_GROUP_CHANGED      = 'customer_group_changed';
    const RULE_TYPE_CUSTOMER_NEW_SUBSCRIPTION   = 'customer_new_subscription';
    const RULE_TYPE_CUSTOMER_UNSUBSCRIPTION   = 'customer_unsubscription';
    const CANCEL_TYPE_CUSTOMER_PLACED_NEW_ORDER = 'customer_new_order';

    public static function toShortOptionArray($extended = FALSE)
    {
        $helper = Mage::helper('followupemail');
        $result = array();

        $orderStatuses = Mage::getSingleton('sales/order_config')->getStatuses();
        foreach ($orderStatuses as $code => $name)
            $result[self::RULE_TYPE_ORDER_STATUS_PREFIX . $code] = $helper->__("Order obtained '%s' status", $name);

        $result[self::RULE_TYPE_CUSTOMER_NEW]               = $helper->__('New customer signup');
        $result[self::RULE_TYPE_CUSTOMER_NEW_SUBSCRIPTION]  = $helper->__('New customer subscription');
        $result[self::RULE_TYPE_CUSTOMER_LOGGED_IN]         = $helper->__('Customer logged in');
        $result[self::RULE_TYPE_CUSTOMER_LAST_ACTIVITY]     = $helper->__('Customer last activity');
        $result[self::RULE_TYPE_CUSTOMER_CAME_BACK_BY_LINK] = $helper->__('Customer came back by link sent');
        $result[self::RULE_TYPE_CUSTOMER_GROUP_CHANGED]     = $helper->__('Customer group changed');
        $result[self::RULE_TYPE_WISHLIST_SHARED]            = $helper->__('Wishlist shared');
        $result[self::RULE_TYPE_WISHLIST_PRODUCT_ADD]       = $helper->__('Product was added to wishlist');
        $result[self::RULE_TYPE_ABANDONED_CART_NEW]         = $helper->__('New abandoned cart appeared');

        if($extended) {
            // Cancellation events
            $result[self::CANCEL_TYPE_CUSTOMER_PLACED_NEW_ORDER] = $helper->__('Customer placed a new order');
            $result[self::RULE_TYPE_CUSTOMER_UNSUBSCRIPTION]     = $helper->__('Customers unsubscription');
        } else {
            // Events
            $result[self::RULE_TYPE_CUSTOMER_BIRTHDAY] = $helper->__('Customer birthday');
        }

        return $result;
    }

    public static function toOptionArray($extended = FALSE)
    {
        $options = self::toShortOptionArray($extended);
        $res = array();

        foreach($options as $k => $v)
            $res[] = array(
                'value' => $k,
                'label' => $v
            );

        return $res;
    }

}