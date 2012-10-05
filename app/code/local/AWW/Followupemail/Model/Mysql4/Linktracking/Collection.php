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


class AWW_Followupemail_Model_Mysql4_Linktracking_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract 
{
    public function _construct() 
    {
        parent::_construct();
        $this->_init('followupemail/linktracking');
    }

    protected static $fields = array(
            'link_visited_at' => 'main_table.visited_at',
            'link_visited_from' => 'main_table.visited_from',

            'email_created_at' => 'e.created_at',
            'email_scheduled_at' => 'e.scheduled_at',
            'email_sent_at' => 'e.sent_at',
            'email_sequence_number' => 'e.sequence_number',
            'email_recipient_name' => 'e.recipient_name',
            'email_recipient_email' => 'e.recipient_email',
            'email_subject' => 'e.subject',

            'rule_event_type' => 'r.event_type',
            'rule_title' => 'r.title',
            'rule_store_ids' => 'r.store_ids',
            'rule_product_type_ids' => 'r.product_type_ids',
            'rule_email_copy_to' => 'r.email_copy_to',
            'rule_sender_name' => 'r.sender_name',
            'rule_sender_email' => 'r.sender_email',
            );

    protected static $cartFields = array(
            'quote_created_at' => 'q.created_at',
            'quote_updated_at' => 'q.updated_at',
            'quote_converted_at' => 'q.converted_at',
            'quote_is_virtual' => 'q.is_virtual',
            'quote_is_multi_shipping' => 'q.is_multi_shipping',
            'quote_items_count' => 'q.items_count',
            'quote_items_qty' => 'q.items_qty',
            'quote_grand_total' => 'q.grand_total',
            'quote_checkout_method' => 'q.checkout_method',
            'quote_customer_email' => 'q.customer_email',
            'quote_customer_prefix' => 'q.customer_prefix',
            'quote_customer_firstname' => 'q.customer_firstname',
            'quote_customer_middlename' => 'q.customer_middlename',
            'quote_customer_lastname' => 'q.customer_lastname',
            'quote_customer_suffix' => 'q.customer_suffix',
            'quote_customer_dob' => 'q.customer_dob',
            'quote_customer_note' => 'q.customer_note',
            'quote_customer_is_guest' => 'q.customer_is_guest',
            'quote_coupon_code' => 'q.coupon_code',
            'quote_subtotal' => 'q.subtotal',
            'quote_subtotal_with_discount' => 'q.subtotal_with_discount',
            );

    protected static $orderFields = array(
            'order_entity_id'                   => 'o.entity_id',
            'order_entity_type_id'              => 'o.entity_type_id',
            'order_attribute_set_id'            => 'o.attribute_set_id',
            'order_increment_id'                => 'o.increment_id',
            'order_parent_id'                   => 'o.parent_id',
            'order_store_id'                    => 'o.store_id',
            'order_created_at'                  => 'o.created_at',
            'order_updated_at'                  => 'o.updated_at',
            'order_is_active'                   => 'o.is_active',
            'order_customer_id'                 => 'o.customer_id',
            'order_tax_amount'                  => 'o.tax_amount',
            'order_shipping_amount'             => 'o.shipping_amount',
            'order_discount_amount'             => 'o.discount_amount',
            'order_subtotal'                    => 'o.subtotal',
            'order_grand_total'                 => 'o.grand_total',
            'order_total_paid'                  => 'o.total_paid',
            'order_total_refunded'              => 'o.total_refunded',
            'order_total_qty_ordered'           => 'o.total_qty_ordered',
            'order_total_canceled'              => 'o.total_canceled',
            'order_total_invoiced'              => 'o.total_invoiced',
            'order_total_online_refunded'       => 'o.total_online_refunded',
            'order_total_offline_refunded'      => 'o.total_offline_refunded',
            'order_base_tax_amount'             => 'o.base_tax_amount',
            'order_base_shipping_amount'        => 'o.base_shipping_amount',
            'order_base_discount_amount'        => 'o.base_discount_amount',
            'order_base_subtotal'               => 'o.base_subtotal',
            'order_base_grand_total'            => 'o.base_grand_total',
            'order_base_total_paid'             => 'o.base_total_paid',
            'order_base_total_refunded'         => 'o.base_total_refunded',
            'order_base_total_qty_ordered'      => 'o.base_total_qty_ordered',
            'order_base_total_canceled'         => 'o.base_total_canceled',
            'order_base_total_invoiced'         => 'o.base_total_invoiced',
            'order_base_total_online_refunded'  => 'o.base_total_online_refunded',
            'order_base_total_offline_refunded' => 'o.base_total_offline_refunded',
            'order_discount_invoiced'           => 'o.discount_invoiced',
            'order_base_discount_invoiced'      => 'o.base_discount_invoiced',
            'order_subtotal_invoiced'           => 'o.subtotal_invoiced',
            'order_tax_invoiced'                => 'o.tax_invoiced',
            'order_shipping_invoiced'           => 'o.shipping_invoiced',
            'order_base_subtotal_invoiced'      => 'o.base_subtotal_invoiced',
            'order_base_tax_invoiced'           => 'o.base_tax_invoiced',
            'order_base_shipping_invoiced'      => 'o.base_shipping_invoiced',
            'order_shipping_tax_amount'         => 'o.shipping_tax_amount',
            'order_base_shipping_tax_amount'    => 'o.base_shipping_tax_amount',
        );


    public static function getRealFieldName($alias)
    {
        $columns = array_merge(self::$fields, self::$cartFields, self::$orderFields);
        return isset($columns[$alias]) ? $columns[$alias] : false;
    }

    public function getLinktrackingCollection($queryType, $fields=false)
    {
        if(!$fields) $fields = self::$fields;
        else $fields = array_merge(self::$fields, is_array($fields)?$fields:array($fields));

        $this->getSelect()
            ->columns($fields)
            ->joinInner(array('e' => $this->getTable('followupemail/queue')),
                        'main_table.queue_id=e.id', '')
            ->joinInner(array('r' => $this->getTable('followupemail/rule')),
                        'e.rule_id=r.id', '');

        switch($queryType)
        {
            case AWW_Followupemail_Model_Source_Linktracking_Types::LINKTRACKING_TYPE_LINK_CART :
                $this->getSelect()
                    ->columns(self::$cartFields)
                    ->joinInner(array('q' => $this->getTable('sales/quote')),
                                'q.entity_id=e.object_id', '')
                    ->where('r.event_type="'.AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW.'"')
                    ->where('q.is_active=1');
                break;

            case AWW_Followupemail_Model_Source_Linktracking_Types::LINKTRACKING_TYPE_LINK_CART_ORDER :
                // Remove some columns for Magento 1.4.x and EE
                if(Mage::helper('followupemail')->checkVersion('1.4')) {
                    unset(self::$orderFields['order_entity_type_id']);
                    unset(self::$orderFields['order_attribute_set_id']);
                    unset(self::$orderFields['order_parent_id']);
                    unset(self::$orderFields['order_is_active']);
                }
                $this->getSelect()
                    ->columns(self::$cartFields)
                    ->columns(self::$orderFields)
                    ->joinInner(array('q' => $this->getTable('sales/quote')),
                                'q.entity_id=e.object_id', '')
                    ->joinInner(array('o' => $this->getTable('sales/order')),
                                'o.increment_id=q.reserved_order_id', '')
                    ->where('r.event_type="'.AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW.'"')
                    ->where('q.is_active=0')
                    ->where('o.created_at>main_table.visited_at');
                break;
        }
        return $this;
    }

    public function getLinktrackingData($field, $key, $queryType='link')
    {
        $obj = $this->getLinktrackingCollection($queryType);
        $obj->getSelect()
                ->where('main_table.id=?', $key)
                ->limit(1);
        $obj->load();

        if(is_array($field)) $field = key($field);

        return $obj->getFirstItem()->getData($field);
    }


    public function getSize()
    {
        if(is_null($this->_totalRecords))
        {
            $this->_renderFilters();
            $sql = $this->getSelect();
            $resource = Mage::getSingleton('core/resource');
            $read = $resource->getConnection('core_read');
            $res = $read->query($sql->assemble())->fetchAll();
            $this->_totalRecords = count($res);
        }
        return intval($this->_totalRecords);
    }

}
