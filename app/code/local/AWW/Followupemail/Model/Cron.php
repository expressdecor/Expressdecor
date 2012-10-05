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


class AWW_Followupemail_Model_Cron
{
    /**
     * Enable debug mode for extended cron events logging
     */
    const DEBUG_MODE = FALSE;

    /**
     * Session timeout after which abandoned cart event may trigger
     */
    const SESSION_TIMEOUT = 3600; // 1 hour

    /**
     * Count of items from orders history collection that will be processed at one pass
     * Also used in check new customers function
     * Miximum value of this constant depends on RAM capacity in your server.
     */
    const ITEMS_PER_ONCE = 50;

    /**
     * ID of cache record with FUE lock
     */
    const CACHE_LOCK_ID = 'aw_hdu_lock';

    /*
     * Cron run interval (in seconds)
     */
    const LOCK_EXPIRE_INTERVAL = 1800; // 30 minutes

    /*
     * @var int Last execution time
     */
    protected $_lastExecTime = false;

    /*
     * @var string Last execution time string representation in MySQL datetime format
     */
    protected $_lastExecTimeMySQL = false;

    /*
     * @var int Time of job start
     */
    protected $_now = false;

    /*
     * @var string Time of job started in MySQL datetime format
     */
    protected $_nowMySQL = false;


    /*
     * Constructor
     */
    public function __construct()
    {
        clearstatcache();
    }

    /**
     * Checks if one FUE is already running
     * @return
     */
    public static function checkLock()
    {
        if (($time = Mage::app()->loadCache(self::CACHE_LOCK_ID))) {
            if ((time() - $time) > self::LOCK_EXPIRE_INTERVAL) {
                // Old expired lock
            } else {
                return false;
            }
        }
        Mage::app()->saveCache(time(), self::CACHE_LOCK_ID, array(), self::LOCK_EXPIRE_INTERVAL);
        return true;
    }

    /*
     * Checks events
     */
    protected function _checkEvents()
    {
        $this->_checkOrderStatusHistory();
        $this->_checkAbandonedCarts();
        $this->_checkCustomerActivity();
    }

    protected function _checkCustomerActivity()
    {
        $this->_checkCustomerLogin();
        $this->_checkCustomerLastActivity();
    }

    /*
     * Runs cron job
     */
    public function cronJobs()
    {
        $config = Mage::getModel('followupemail/config');

        if (!$this->_lastExecTime = $config->getParam(AWW_Followupemail_Model_Config::LAST_EXEC_TIME)) {
            $config->setParam(AWW_Followupemail_Model_Config::LAST_EXEC_TIME, time());
            if (!self::DEBUG_MODE) return;
        }

        $this->_now = time();

        if (!self::checkLock()) {
            AWW_Followupemail_Model_Log::log('FUE is already running');
            if (!self::DEBUG_MODE) return;
        }

        AWW_Followupemail_Model_Sender::sendPrepared();

        $this->_nowMySQL = date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $this->_now);
        $this->_lastExecTimeMySQL = date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $this->_lastExecTime);

        try {
            $timeShift = Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS);

            AWW_Followupemail_Model_Log::log('cron started, last execution time is '
                . date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT,
                    $this->_lastExecTime + $timeShift));

            $this->_removeOldCoupons();
            $this->_checkEvents();
            $config->setParam(AWW_Followupemail_Model_Config::LAST_EXEC_TIME, $this->_now);
        }
        catch (Exception $e) {
            Mage::logException($e);
        }

        Mage::app()->removeCache(self::CACHE_LOCK_ID);
        $timeShift = Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS);
        AWW_Followupemail_Model_Log::log('cron stopped at ' . date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, time() + $timeShift) .
            '. Last time is ' . date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $this->_now + $timeShift));
    }

    /**
     * Removes old coupons, generated by FUE
     */
    protected function _removeOldCoupons()
    {        
        if (!Mage::helper('followupemail/coupon')->canUseCoupons()) { return; }
        if (self::DEBUG_MODE) AWW_Followupemail_Model_Log::log('Removing old coupons');
        $timeShift = Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS);
        $expires = date(AWW_Followupemail_Helper_Coupon::MYSQL_DATETIME_FORMAT, time() + $timeShift);
        $collection = Mage::getModel('salesrule/coupon')->getCollection();
        $collection->getSelect()->joinLeft(array('scr' => $collection->getTable('salesrule/rule')), 'main_table.rule_id = scr.rule_id')
            ->where('scr.coupon_type = ?', Mage::helper('followupemail/coupon')->getFUECouponsCode())
            ->where('expiration_date <= ?', $expires);

        if (self::DEBUG_MODE) AWW_Followupemail_Model_Log::log(sprintf("Total %d old coupons found", $collection->getSize()));

        $where = $collection->getConnection()->quoteInto('coupon_id IN (?)', $collection->getAllIds());
        $collection->getConnection()->delete($collection->getMainTable(), $where);

        if (self::DEBUG_MODE) AWW_Followupemail_Model_Log::log('Completed removing old coupons');
    }

    /*
     * Checks order status history
     */
    protected function _checkOrderStatusHistory()
    {
        if (self::DEBUG_MODE) AWW_Followupemail_Model_Log::log("Processing Order Status History");
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');

        $currentPage = 1;
        $_pages = 1;
        while ($currentPage <= $_pages) {
            if (self::DEBUG_MODE) AWW_Followupemail_Model_Log::log("Processing page {$currentPage} from {$_pages} total");
            $statusHistoryCollection = Mage::getModel('sales/order_status_history')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('created_at', array('gt' => $this->_lastExecTimeMySQL))
                ->addAttributeToFilter('created_at', array('lt' => $this->_nowMySQL))
                ->setPageSize(self::ITEMS_PER_ONCE)
                ->setCurPage($currentPage)
                ->load();

            if (!$statusHistoryCollection->getSize()) return;

            if ($_pages == 1 && ceil($statusHistoryCollection->getSize() / self::ITEMS_PER_ONCE) > 1)
                $_pages = (int)ceil($statusHistoryCollection->getSize() / self::ITEMS_PER_ONCE);

            $order = Mage::getModel('sales/order');
            $queue = Mage::getResourceModel('followupemail/queue');
            $dbReader = Mage::getResourceModel('followupemail/rule');

            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');
            $select = $read->select()
                ->distinct()
                ->from(array('qio' => $resource->getTableName('sales/quote_item_option')), '')
                ->joinInner(array('qi' => $resource->getTableName('sales/quote_item')), 'qi.item_id=qio.item_id', '')
                ->joinInner(array('q' => $resource->getTableName('sales/quote')), 'q.entity_id=qi.quote_id', '')
                ->joinInner(array('o' => $resource->getTableName('sales/order')), 'o.increment_id=q.reserved_order_id', '')
                ->columns('qio.value')
                ->where('qio.code="product_type"');

            foreach ($statusHistoryCollection->getItems() as $historyItem) {
                $eventName = AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ORDER_STATUS_PREFIX . $historyItem->getStatus();

                $order->reset()->load($historyItem->getParentId());
                if (($eventName == AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ORDER_STATUS_PREFIX . 'pending') ||
                    ($eventName == AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ORDER_STATUS_PREFIX . 'pending_payment')
                )
                    $queue->cancelByEvent($order->getCustomerEmail(), AWW_Followupemail_Model_Source_Rule_Types::CANCEL_TYPE_CUSTOMER_PLACED_NEW_ORDER, $order->getId());

                $queue->cancelByEvent($order->getCustomerEmail(), $eventName, $order->getId());
                $queue->cancelByEvent($order->getCustomerEmail(), $eventName, $order->getIncrementId());
                $queue->cancelByEvent($order->getCustomerEmail(), $eventName, $order->getQuoteId());

                $ruleIds = $dbReader->getRuleIdsByEventType($eventName);
                foreach ($ruleIds as $key => $value)
                    if ($dbReader->isOrderStatusProcessed($historyItem->getParentId(), $value)) {
                        AWW_Followupemail_Model_Log::log('order status duplicated, orderId=' . $historyItem->getParentId() . ' status=' . $historyItem->getStatus() . ' ruleId=' . $value);
                        unset($ruleIds[$key]);
                    }

                if (!empty($ruleIds)) {

                    $productIds = array();
                    $categoryIds = '';
                    $productTypeIds = array();
                    $sku = array();

                    $orderItemProduct = Mage::getModel('catalog/product');
                    $extraInfo = $read->fetchCol($select->where('o.entity_id=?', $order->getId()));

                    foreach ($extraInfo as $productTypeId)
                        $productTypeIds[$productTypeId] = true;

                    foreach ($order->getAllItems() as $orderItem) {
                        $orderItemProduct->unsetData()->load($orderItem->getProductId());

                        $ids = $orderItemProduct->getCategoryIds();
                        if (is_array($ids)) $ids = implode(',', $ids);
                        $categoryIds .= ',' . $ids;

                        $productTypeIds[$orderItemProduct->getTypeId()] = true;
                        $sku[] = $orderItem->getSku();
                        $productIds[] = $orderItem->getId();
                    }

                    $params = array();
                    $params['store_id'] = $order->getStoreId();
                    $params['category_ids'] = Mage::helper('followupemail')->noEmptyValues(array_unique(explode(',', $categoryIds)));
                    $params['product_type_ids'] = array_keys($productTypeIds);
                    $params['sku'] = $sku;
                    $params['product_ids'] = $productIds;

                    $customerId = $order->getCustomerId();
                    if ($customerId)
                        $params['customer_id'] = $customerId;
                    else
                        $params['customer_email'] = $order->getCustomerEmail();

                    foreach ($ruleIds as $ruleId) {
                        AWW_Followupemail_Model_Log::log("order status orderId={$order->getId()} validating, ruleId=$ruleId");

                        $objects = array();
                        $objects['object_id'] = $order->getId();
                        $objects['order_id'] = $order->getId();
                        $objects['order'] = $order;
                        $objects['customer_is_guest'] = $order->getCustomerIsGuest();

                        Mage::getModel('followupemail/rule')->load($ruleId)->process($params, $objects);
                    }
                }
            }

            unset($statusHistoryCollection);
            $currentPage++;
        }
    }

    /*
     * Checks for new abandoned carts appeared
     */
    protected function _checkAbandonedCarts()
    {
        if (self::DEBUG_MODE) AWW_Followupemail_Model_Log::log("Checking abandoned carts");
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');

        $select = $read->select()
            ->from(array('q' => $resource->getTableName('sales/quote')), array(
            'store_id' => 'q.store_id',
            'quote_id' => 'q.entity_id',
            'customer_id' => 'q.customer_id',
            'updated_at' => 'q.updated_at'))
            ->joinLeft(array('a' => $resource->getTableName('sales/quote_address')),
            'q.entity_id=a.quote_id AND a.address_type="billing"',
            array(
                'customer_email' => new Zend_Db_Expr('IFNULL(q.customer_email, a.email)'),
                'customer_firstname' => new Zend_Db_Expr('IFNULL(q.customer_firstname, a.firstname)'),
                'customer_middlename' => new Zend_Db_Expr('IFNULL(q.customer_middlename, a.middlename)'),
                'customer_lastname' => new Zend_Db_Expr('IFNULL(q.customer_lastname, a.lastname)'),
            ))
            ->joinInner(array('i' => $resource->getTableName('sales/quote_item')), 'q.entity_id=i.quote_id', array(
            'product_ids' => new Zend_Db_Expr('GROUP_CONCAT(i.product_id)'),
            'item_ids' => new Zend_Db_Expr('GROUP_CONCAT(i.item_id)')
        ))
            ->where('q.is_active=1')
            ->where('q.updated_at > ?', date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT,
            $this->_lastExecTime - self::SESSION_TIMEOUT))
            ->where('q.updated_at < ?', date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT,
            $this->_now - self::SESSION_TIMEOUT))
            ->where('q.items_count>0')
            ->where('q.customer_email IS NOT NULL OR a.email IS NOT NULL')
            ->where('i.parent_item_id IS NULL')
            ->group('q.entity_id')
            ->order('updated_at');

        $carts = $read->fetchAll($select);
        if (!count($carts)) return;

        $queue = Mage::getResourceModel('followupemail/queue');
        foreach ($carts as $cart)
            $queue->cancelByEvent($cart['customer_email'], AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW, $cart['quote_id']);

        $ruleIds = Mage::getModel('followupemail/mysql4_rule')->getRuleIdsByEventType(AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW);
        if (!count($ruleIds)) return;

        $product = Mage::getModel('catalog/product');

        $select = $read->select()
            ->distinct()
            ->from($resource->getTableName('sales/quote_item_option'), 'value')
            ->where('code="product_type"');

        foreach ($carts as $cart) {
            $categoryIds = '';
            $productTypeIds = array();
            $sku = array();
            $productIds = explode(',', $cart['product_ids']);
            $extraInfo = $read->fetchCol($select->where('item_id IN (' . $cart['item_ids'] . ')'));

            foreach ($extraInfo as $productTypeId)
                $productTypeIds[$productTypeId] = true;

            foreach ($productIds as $productId) {
                $product->unsetData()->load($productId);
                if (is_array($product->getCategoryIds()))
                    $categoryIds .= ',' . implode(',', $product->getCategoryIds());
                else $categoryIds .= ',' . $product->getCategoryIds();
                $productTypeIds[$product->getTypeId()] = true;
                $sku[] = $product->getSku();
            }

            $params = array();
            $params['store_id'] = $cart['store_id'];
            $params['customer_id'] = $cart['customer_id'];
            $params['customer_email'] = $cart['customer_email'];
            $params['category_ids'] = Mage::helper('followupemail')->noEmptyValues(array_unique(explode(',', $categoryIds)));
            $params['product_type_ids'] = array_keys($productTypeIds);
            $params['sku'] = $sku;
            $params['product_ids'] = $productIds;
            $params['object_id'] = $cart['quote_id'];
            $params['quote_id'] = $cart['quote_id'];

            foreach ($ruleIds as $ruleId) {
                AWW_Followupemail_Model_Log::log('carts abandoned quoteId=' . $cart['quote_id'] . ' validating, ruleId=' . $ruleId);

                $objects = array();
                $objects['customer_firstname'] = $cart['customer_firstname'];
                $objects['customer_middlename'] = $cart['customer_middlename'];
                $objects['customer_lastname'] = $cart['customer_lastname'];
                $objects['updated_at'] = $cart['updated_at'];
                $objects['customer_is_guest'] = (int)!$cart['customer_id'];

                Mage::getModel('followupemail/rule')->load($ruleId)->process($params, $objects);
            }
        }
    }

    protected function _checkCustomerLastActivity()
    {
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getModel('log/visitor')->getCollection();

        if (Mage::helper('followupemail')->checkExtensionVersion('Mage_Log', '0.7.7', '<=')) {
            $collection->getSelect()->from(array('main_table' => $resource->getTableName('log/visitor')),
                array('first_visit_at', 'last_visit_at', 'last_url_id', 'store_id'));
        }

        $collection->getSelect()->join(array('c' => $resource->getTableName('log/customer')),
            'main_table.visitor_id=c.visitor_id', array('customer_id', 'login_at', 'logout_at', 'store_id'))
            ->where('last_visit_at BETWEEN "' . $this->_lastExecTimeMySQL . '" AND "' . $this->_nowMySQL . '"')
            ->joinLeft(array('u' => $resource->getTableName('log/url_info_table')),
            'main_table.last_url_id=u.url_id',
            'url')
            ->group('c.customer_id');

        $this->_processCustomerActivity($collection, AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_LAST_ACTIVITY);
    }

    protected function _checkCustomerLogin()
    {
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getModel('log/visitor')->getCollection();

        if (Mage::helper('followupemail')->checkExtensionVersion('Mage_Log', '0.7.7', '<=')) {
            $collection->getSelect()->from(array('main_table' => $resource->getTableName('log/visitor')),
                array('first_visit_at', 'last_visit_at', 'last_url_id', 'store_id'));
        }

        $collection->getSelect()->join(array('c' => $resource->getTableName('log/customer')),
            'main_table.visitor_id=c.visitor_id',
            array('customer_id', 'login_at', 'logout_at', 'store_id'))
            ->where('`login_at` BETWEEN "' . $this->_lastExecTimeMySQL . '" AND "' . $this->_nowMySQL . '" ')
            ->group('c.customer_id');

        $this->_processCustomerActivity($collection, AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_LOGGED_IN);

    }

    protected function _processCustomerActivity($collection, $ruleType)
    {
        $queue = Mage::getResourceModel('followupemail/queue');
        foreach ($collection as $visit) {
            $queue->cancelByEvent(
                $queue->getEmailByCustomerId($visit['customer_id']),
                $ruleType,
                $visit['customer_id']
            );
        }

        $ruleIds = Mage::getModel('followupemail/mysql4_rule')
            ->getRuleIdsByEventType($ruleType);

        if (!count($ruleIds)) return;

        $params = array();
        $objects = array();

        foreach ($collection as $visit) {
            $params['customer_id'] = $visit['customer_id'];
            $params['store_id'] = $visit['store_id'];
            $objects['object_id'] = $visit['customer_id'];
            // for login
            if (isset($visit['login_at'])) $objects['last_login_at'] = $visit['login_at'];
            // next two for logout
            if (isset($visit['last_visit_at'])) $objects['last_visit_time'] = $visit['last_visit_at'];
            if (isset($visit['url'])) $objects['url_last_visited'] = $visit['url'];

            foreach ($ruleIds as $ruleId) {
                AWW_Followupemail_Model_Log::log('customer last activity' . " customerId={$params['customer_id']} validating, ruleId=$ruleId");
                Mage::getModel('followupemail/rule')->load($ruleId)->process($params, $objects);
            }
        }
    }

/*
* Checks for new customers registered
* deprecated with new method AWW_Followupemail_Model_Events::checkCustomer($event)
*/
/* protected function _checkNewCustomer() {
    if(self::DEBUG_MODE) AWW_Followupemail_Model_Log::log('Checking new customers');

    $_count = Mage::getModel('customer/customer')
                                ->getCollection()
                                ->addAttributeToSelect('*')
                                ->addAttributeToFilter('created_at',array('from'=>$this->_lastExecTimeMySQL,'to'=>$this->_nowMySQL))
                                ->count();
    $notconfirmed_count = 0;
    $notconfirmed_count = Mage::getModel('customer/customer')
                                ->getCollection()
                                ->addAttributeToSelect('*')
                                ->addAttributeToFilter('confirmation',array('notnull'=>false))
                                ->addAttributeToFilter('created_at',array('from'=>$this->_lastExecTimeMySQL,'to'=>$this->_nowMySQL))
                                ->count();
    $_count=$_count-$notconfirmed_count;
    if(!$_count) return;

    $_pages = ceil($_count / self::ITEMS_PER_ONCE);
    $currentPage = 1;
    if(self::DEBUG_MODE) AWW_Followupemail_Model_Log::log("Processing New Customers. Total {$_count} customers found");

    while($currentPage <= $_pages) {
        if(self::DEBUG_MODE) AWW_Followupemail_Model_Log::log("Processing New Customers. Page {$currentPage} of {$_pages} total");
    $customers_collection = Mage::getModel('customer/customer')
                                ->getCollection()
                                ->addAttributeToSelect('*')
                                ->addAttributeToFilter('created_at',array('from'=>$this->_lastExecTimeMySQL,'to'=>$this->_nowMySQL))
                                ->setPageSize(self::ITEMS_PER_ONCE)->setCurPage($currentPage);

        if(!count($customers_collection)) return;

        $queue = Mage::getResourceModel('followupemail/queue');
        foreach($customers_collection as $customer)
            $queue->cancelByEvent(
                $queue->getEmailByCustomerId($customer['entity_id']),
                AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_NEW,
                $customer['entity_id']
            );

        $ruleIds = Mage::getModel('followupemail/mysql4_rule')
            ->getRuleIdsByEventType(AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_NEW);

        if(!count($ruleIds)) return;

        $params = array();
        $objects = array();

        foreach($customers_collection as $customer) {
            if(!$customer->getConfirmation())
            {
                $params['customer_id'] = $customer['entity_id'];
                $params['store_id'] = $customer['store_id'];

                $objects['object_id'] = $customer['entity_id'];

                foreach($ruleIds as $ruleId) {
                    AWW_Followupemail_Model_Log::log('customer registered, customerId='.$params['customer_id']." validating, ruleId=$ruleId");

                    Mage::getModel('followupemail/rule')->load($ruleId)->process($params, $objects);
                }
            }
        }

        $currentPage++;
    }
}*/
}
