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


class AWW_Followupemail_Model_Events
{
    const NEWSLETTER_SUBSCRIPTION_PROCESSED_FLAG = 'AWW_fue_customer_subscription_already_processed';

    /*
    * Runs when a product was added to wishlist
    */
    public function wishlistProductAdd($eventData)
    {
        $product = $eventData->getProduct();
        $wishlist = $eventData->getWishlist();

        $queue = Mage::getResourceModel('followupemail/queue');
        $queue->cancelByEvent(
            $queue->getEmailByCustomerId($wishlist->getCustomerId()),
            AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_WISHLIST_PRODUCT_ADD,
            $product->getId()
        );

        if (count($ruleIds = Mage::getModel('followupemail/mysql4_rule')
            ->getRuleIdsByEventType(AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_WISHLIST_PRODUCT_ADD))
        ) {
            $store = $wishlist->getStore();

            $params = array();
            $params['store_id'] = $store->getId();
            $params['customer_id'] = $wishlist->getCustomerId();
            $params['product_type_ids'] = array($product->getTypeId());
            $params['category_ids'] = (is_array($ids = $product->getCategoryIds()) ? $ids : explode(',', $ids));
            $params['sku'] = array($product->getSku());
            $params['product_ids'] = array($product->getId());

            $objects = array();

            foreach ($ruleIds as $ruleId)
            {
                $objects['object_id'] = $product->getId();
                $objects['product_id'] = $product->getId();
                $objects['product'] = $product;
                $objects['wishlist_id'] = $wishlist->getWishlistId();
                $objects['wishlist'] = $wishlist;

                Mage::getModel('followupemail/rule')
                    ->load($ruleId)
                    ->process($params, $objects);
            }
        }
    }

    /*
     * Runs when wishlist was shared
     */
    public function wishlistShared($eventData)
    {
        $wishlist = $eventData->getWishlist();

        $queue = Mage::getResourceModel('followupemail/queue');
        $queue->cancelByEvent(
            $queue->getEmailByCustomerId($wishlist->getCustomerId()),
            AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_WISHLIST_SHARED,
            $wishlist->getWishlistId()
        );

        if (count($ruleIds = Mage::getModel('followupemail/mysql4_rule')
            ->getRuleIdsByEventType(AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_WISHLIST_SHARED))
        ) {
            $store = $wishlist->getStore();
            $products = $wishlist->getProductCollection();

            $productTypes = array();
            $productIds = array();
            $categoryIds = array();
            $sku = array();

            foreach ($products->getItems() as $product)
            {
                $productTypes[$product->getTypeId()] = true;
                $productIds[$product->getId()] = true;
                $cats = $product->getCategoryIds();
                $categoryIds = array_merge($categoryIds, is_array($cats) ? $cats : explode(',', $cats));
                $sku[] = $product->getSku();
            }

            $params = array();
            $params['store_id'] = $store->getId();
            $params['customer_id'] = $wishlist->getCustomerId();
            $params['product_type_ids'] = array_keys($productTypes);
            $params['category_ids'] = array_unique($categoryIds);
            $params['sku'] = $sku;
            $params['product_ids'] = $productIds;

            $objects = array();

            $rule = Mage::getModel('followupemail/rule');

            foreach ($ruleIds as $ruleId)
            {
                $objects['object_id'] = $wishlist->getWishlistId();
                $objects['wishlist_id'] = $wishlist->getWishlistId();
                $objects['wishlist'] = $wishlist;

                Mage::getModel('followupemail/rule')
                    ->load($ruleId)
                    ->process($params, $objects);
            }
        }
    }

    /*
     * Runs when customer comes by link sent in email
     */
    public function customerCameBack($param)
    {
        if ($param instanceof AWW_Followupemail_Model_Queue) $queue = $param;
        else $queue = Mage::getResourceModel('followupemail/queue')->load($param);

        $queueResource = $queue->getResource();
        $queueResource->cancelByEvent(
            $queueResource->getEmailByCustomerId($queue->getRecipientEmail()) ? $queueResource->getEmailByCustomerId($queue->getRecipientEmail()) : $queue->getRecipientEmail(),
            AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_CAME_BACK_BY_LINK,
            $queue->getObjectId()
        );

        if (count($ruleIds = Mage::getModel('followupemail/mysql4_rule')
            ->getRuleIdsByEventType(AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_CAME_BACK_BY_LINK))
        ) {
            $rule = Mage::getModel('followupemail/rule')->load($queue->getRuleId());

            $params = array();
            $params['store_id'] = Mage::app()->getStore()->getId();
            $params['customer_email'] = $queue->getRecipientEmail();

            $objects = array();
            $objects['object_id'] = $queue->getObjectId();
            $objects['queue'] = $queue;
            $objects['rule'] = $rule;

            foreach ($ruleIds as $ruleId)
            {
                Mage::getModel('followupemail/rule')
                    ->load($ruleId)
                    ->process($params, $objects);
            }
        }
    }

    public function customerAfterSave($event)
    {
        $customer = $event->getDataObject();
        if ($customer->getData('group_id') != $customer->getOrigData('group_id') && $customer->getOrigData('group_id')) {
            $queue = Mage::getResourceModel('followupemail/queue');
            $queue->cancelByEvent($customer->getEmail(),
                AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_GROUP_CHANGED);
            //Customer group has been changed event processing
            $ruleIds = Mage::getModel('followupemail/mysql4_rule')
                ->getRuleIdsByEventType(AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_GROUP_CHANGED);
            if (count($ruleIds)) {
                $params = array(
                    'customer_id' => $customer->getId(),
                    'store_id' => $customer->getStoreId(),
                    'customer_new_group' => Mage::getModel('customer/group')->load($customer->getData('group_id'))->getData('customer_group_code')
                );
                $objects = array(
                    'object_id' => $customer->getId()
                );

                foreach ($ruleIds as $ruleId) {
                    Mage::getModel('followupemail/rule')->load($ruleId)->process($params, $objects);
                }
            }
        }
    }

    public function addFUECouponType($event)
    {
        if (!Mage::helper('followupemail/coupon')->canUseCoupons()) return;
        $couponTypes = $event->getTransport()->getCouponTypes();
        $couponTypes[Mage::helper('followupemail/coupon')->getFUECouponsCode()] = Mage::helper('followupemail')->__('FUE Generated Coupons');
        $event->getTransport()->setCouponTypes($couponTypes);
    }

    public function checkCouponsMenu($event)
    {
        if (Mage::helper('followupemail/coupon')->canUseCoupons()) return;
        $_t1 = Mage::getConfig()->getNode('adminhtml/menu/followupemail/children');
        if (method_exists(Mage::getSingleton('admin/config'), 'getAdminhtmlConfig'))
            $_t2 = Mage::getSingleton('admin/config')->getAdminhtmlConfig()->getNode('menu/followupemail/children');
        if (isset($_t1) && $_t1 && isset($_t1->coupons)) unset($_t1->coupons);
        if (isset($_t2) && $_t2 && isset($_t2->coupons)) unset($_t2->coupons);
    }

    /*
     * Processing customer subscription/unsubscription for native newsletter and Advanced Newsletter
     */
    public function newsletterSubscribe($event)
    {
        if (Mage::registry(self::NEWSLETTER_SUBSCRIPTION_PROCESSED_FLAG)) return;
        Mage::register(self::NEWSLETTER_SUBSCRIPTION_PROCESSED_FLAG, TRUE);

        $subscriber = $event->getSubscriber();
        $queue = Mage::getResourceModel('followupemail/queue');
        $status_key = 'subscriber_status';
        $email_key = 'subscriber_email';
        if (Mage::helper('followupemail')->canUseAN())
            if (class_exists('AWW_Advancednewsletter_Model_Subscriber'))
                if ($subscriber instanceof AWW_Advancednewsletter_Model_Subscriber) {
                    $status_key = 'status';
                    $email_key = 'email';
                }
        $queue->cancelByEvent($subscriber->getData($email_key),
            AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_UNSUBSCRIPTION);
        $ruleIds = Mage::getModel('followupemail/mysql4_rule')
            ->getRuleIdsByEventType(AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_NEW_SUBSCRIPTION);
        if (count($ruleIds)) {
            $params = array(
                'customer_id' => $subscriber->getCustomerId(),
                'store_id' => $subscriber->getStoreId(),
                'customer_email' => $subscriber->getEmail(),
                'subscriber_status' => $subscriber->getData($status_key)
            );
            $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());
            $objects = array(
                'object_id' => $customer->getId()
            );
            foreach ($ruleIds as $ruleId) {
                if ($subscriber->getData($status_key) === 1) //process queue if subscription status is active
                    Mage::getModel('followupemail/rule')->load($ruleId)->process($params, $objects);
            }
        }
    }

    public function deleteSubscriber($event)
    {
        $subscriber = $event->getSubscriber();
        $queue = Mage::getResourceModel('followupemail/queue');
        $email_key = 'subscriber_email';
        if (Mage::helper('followupemail')->canUseAN())
            if (class_exists('AWW_Advancednewsletter_Model_Subscriber'))
                if ($subscriber instanceof AWW_Advancednewsletter_Model_Subscriber) {
                    $email_key = 'email';
                }
        $queue->cancelByEvent($subscriber->getData($email_key),
            AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_UNSUBSCRIPTION);
    }

    /**
     * @param $customer
     * @return bool
     * Checks is new customer registered or confirmed
     */
    private function isNewCustomer($customer)
    {
        $re = Mage::app()->getRequest();
        $action = $re->getActionName();
        $c1 = $customer->getData('confirmation');
        $c2 = $customer->getOrigData('confirmation');
        $check = Mage::getModel('customer/customer')->load($customer['entity_id']);
        if ($action == 'createpost' || $action == 'confirm') {
            if ($check->isConfirmationRequired() == false || ($c1 == null && $c2 != null))
                return true;
            else
                return false;
        }
        if ($action == 'saveOrder') {
            if ($c1 == null && $c2 == null && $customer->getData('password'))
                return true;
        }
        else
            if ($c1 == null && $c2 != null)
                return true;
    }

    public function checkCustomer($event)
    {
        if (!Mage::registry('checked_customer')) {
            $customer = $event->getCustomer();
            if (self::isNewCustomer($customer) == true) {
                $queue = Mage::getResourceModel('followupemail/queue');
                $queue->cancelByEvent($customer['email'],
                    AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_NEW,
                    $customer['entity_id']
                );
                $ruleIds = Mage::getModel('followupemail/mysql4_rule')
                    ->getRuleIdsByEventType(AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_NEW);

                if (!count($ruleIds)) return;

                $params = array();
                $objects = array();
                $params['customer_id'] = $customer['entity_id'];
                $params['store_id'] = $customer['store_id'];
                $objects['object_id'] = $customer['entity_id'];
                foreach ($ruleIds as $ruleId) {
                    AWW_Followupemail_Model_Log::log('customer registered, customerId=' . $params['customer_id'] . " validating, ruleId=$ruleId");
                    Mage::getModel('followupemail/rule')->load($ruleId)->process($params, $objects);
                }
            }
            Mage::register('checked_customer', '1');
        }
    }
}
