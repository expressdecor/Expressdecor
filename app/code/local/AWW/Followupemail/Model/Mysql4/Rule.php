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


class AWW_Followupemail_Model_Mysql4_Rule extends Mage_Core_Model_Mysql4_Abstract 
{
    public function _construct()
    {
       $this->_init('followupemail/rule', 'id');
    }

    public function getRuleIdsByEventType($eventType)
    {
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from($this->getMainTable(), 'id')
            ->where('event_type=?', $eventType)
            ->where('is_active=1');

        return $res = $db->fetchCol($select);
    }

    public function getTemplateContent($modelName, $templateName, $fieldNames = array(
        'subject' => 'template_subject',
        'content' => 'template_text',
        'sender_name' => 'template_sender_name',
        'sender_email' => 'template_sender_email'))
    {
        $db = $this->_getReadAdapter();

        $select = $db->select()
                    ->from($this->getTable($modelName), $fieldNames)
                    ->where('template_id=?', $templateName)
                    ->orwhere('template_code=?', $templateName)
                    ->limit(1);

        return $db->fetchRow($select);
    }

    public function isOrderStatusProcessed($orderId, $ruleId)
    {
        $db = $this->_getReadAdapter();

        $select = $db->select()
            ->from($this->getTable('followupemail/queue'), 'id')
            ->where('object_id=?', $orderId)
            ->where('rule_id=?', $ruleId)
            ->limit(1);

        return $db->fetchOne($select);
    }


    const ADVANCED_NEWSLETTER_SEGMENTS_ALL = 'ALL_SEGMENTS';
    
    /**
     * Getting segments list from AN
     * @return array
     */
    public function getAdvancedNewsletterSegmentList() {
        if(!Mage::helper('followupemail')->canUseAN()) return array();
        $segments = Mage::getModel('advancednewsletter/api')->getSegmentsCollection();
        $_segments = array();
        foreach($segments as $segment)
            $_segments[] = array(
                'value' => $segment->getCode(),
                'label' => $segment->getTitle()
            );
        return $_segments;
    }
}
