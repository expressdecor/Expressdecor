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


class AWW_Followupemail_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract 
{
    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  _construct
    public function _construct() 
    {
       $this->_init('followupemail/queue', 'id');
    }

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - getPreparedEmailIds
    public function getPreparedEmailIds()
    {
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from($this->getMainTable(), array('id'))
            ->where('scheduled_at<=?', date(self::MYSQL_DATETIME_FORMAT, time()))
            ->where('status="R"');

        return $db->fetchAll($select);
    }

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - deleteByCustomerEmail
    public function deleteByCustomerEmail($email)
    {
        $now = date(self::MYSQL_DATETIME_FORMAT, time());
        $db = $this->_getWriteAdapter();
        $db->delete($this->getMainTable(), 
            'recipient_email='.$db->quote($email))." AND status<>'S' AND (scheduled_at>='$now' OR sent_at>='$now')";
    }

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - getFromFields
    public function getFromFields($ruleId)
    {
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from($this->getTable('followupemail/rule'), array(
                // 'sender_email', 'sender_name',
                    'email_copy_to',
                    'email_send_to_customer'
                ))
            ->where('id=?', $ruleId)
            ->limit(1);

        return $db->fetchRow($select);
    }

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  cancel
    public function cancel($id)
    {
        $db = $this->_getWriteAdapter();
        $db->query('UPDATE '.$this->getMainTable().'SET status=\'C\' WHERE id='.$db->quote($id));
    }

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - getIdByCode
    public function getIdByCode($code)
    {
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from($this->getMainTable(), 'id')
            ->where('code=?', $code)
            ->limit(1);

        return $db->fetchOne($select);
    }

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - cancelByEvent
// 'passive' logic version, deletes the emails from the queue by their rules' eventType
    // public function cancelByEvent($email, $eventType, $objectId)
    public function cancelByEvent($email, $eventType, $objectId=false)
    {
       
        $db = $this->_getReadAdapter();
        
        $abandonedStatus = AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW;

        $select = $db->select()
            ->from(array('e' => $this->getTable('followupemail/queue')), array(
                    'ids' => new Zend_Db_Expr('GROUP_CONCAT(e.id)'),
                ))
            ->joinInner(array('r' => $this->getTable('followupemail/rule')), 'r.id=e.rule_id', '')
            ->where('e.object_id=?', $objectId)
            ->where('e.recipient_email=?', $email)
            ->where("find_in_set(?, r.cancel_events) OR (r.event_type = '$abandonedStatus')", $eventType); // !!! important : 'passive' logic!
        
        /*
         * Event related with changing order status. In this case only letters
         * with specified order_id in params will be removed
         */
        if(!(strpos($eventType, AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ORDER_STATUS_PREFIX)===FALSE)) {
            $select->where('e.object_id = ?', $objectId);
        }
        
       
        $queueIds = $db->fetchOne($select);
        if(!$queueIds) return; 

        Mage::getSingleton('followupemail/log')->logWarning("cancelling email ids=$queueIds by event=$eventType for email=$email", $this);

        $db = $this->_getWriteAdapter();
        $query = "DELETE FROM `{$this->getMainTable()}`"
                ." WHERE id IN ($queueIds)"
                ." AND status<>'".AWW_Followupemail_Model_Source_Queue_Status::QUEUE_STATUS_SENT."'";
      
        $db->query($query);
    }

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  getEmailByCustomerId
    public function getEmailByCustomerId($customerId)
    {
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from($this->getTable('customer/entity'), 'email')
            ->where('entity_id=?', $customerId)
            ->limit(1);

        return $db->fetchOne($select);
    }
}