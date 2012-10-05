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


class AWW_Followupemail_Model_Crondaily
{
    /*
     * @var bool Whether FUE daily job is currently running
     */
    protected static $_isRunning = false;

    /*
     * @var int Minimal interval between cron run (if started manually)
     */
    public $cronMinInterval = 86400; // 1 day


    /*
     * Class constructor
     */
    public function __construct()
    {
        clearstatcache();
    }

    /*
     * Checks for new events
     */
    protected function _checkEvents()
    {
        $this->_checkBirthdays();
    }

    /*
     * Runs daily cron job
     */
    public function cronJobs()
    {
        $config = Mage::getModel('followupemail/config');

        if(!$lastExecTime = $config->getParam(AWW_Followupemail_Model_Config::LAST_EXEC_TIME_DAILY))
        {
            $config->setParam(AWW_Followupemail_Model_Config::LAST_EXEC_TIME_DAILY, time());
            if(AWW_Followupemail_Model_Cron::DEBUG_MODE)
                AWW_Followupemail_Model_Log::log('CD Never runs');
            return;
        }

        $now = time();

        if( self::$_isRunning
        ||  $now - $lastExecTime < $this->cronMinInterval
        ) {
            if(AWW_Followupemail_Model_Cron::DEBUG_MODE)
                AWW_Followupemail_Model_Log::log('CD Already running or minimal interval isn\'t match '.date('d.m.Y H:i:s', $lastExecTime).' - '.($this->cronMinInterval - $now - $lastExecTime));
            return;
        }

        self::$_isRunning = true;

        try
        {
            $timeShift = Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS);

            AWW_Followupemail_Model_Log::log('cron daily started, last execution time is '
                .date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, 
                        $lastExecTime + $timeShift));

            $this->_checkEvents();

            $config->setParam(AWW_Followupemail_Model_Config::LAST_EXEC_TIME_DAILY, $now);
        }
        catch (Exception $e) { Mage::logException($e); }

        self::$_isRunning = false;
        AWW_Followupemail_Model_Log::log('Daily cron stopped ');
    }

    /*
     * Checks for customer birthdays
     */
    protected function _checkBirthdays()
    {

        $ruleIds = Mage::getModel('followupemail/mysql4_rule')
                ->getRuleIdsByEventType(AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_CUSTOMER_BIRTHDAY);

        if(!count($ruleIds)) return;

        $customerEntityTypeID = Mage::getModel('eav/entity_type')->loadByCode('customer')->getId();
        $customerDateOfBirthAttributeId = Mage::getModel('eav/entity_attribute')->loadByCode($customerEntityTypeID, 'dob')->getId();

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');

        $customer_entity = $resource->getTableName('customer/entity');
        $dobTableName = $customer_entity.'_datetime';

        $time = time();

        foreach($ruleIds as $ruleId)
        {
            $rule = Mage::getModel('followupemail/rule')->load($ruleId);

            $sequenceNumber = 1;
            foreach(unserialize($rule->getChain()) as $chain)
            {
                $select = $read->select()
                    ->from(array('dob' => $dobTableName), array('entity_id', 'value'))
                    ->join(array('customer' => $customer_entity),
                        'customer.entity_id=dob.entity_id AND customer.entity_type_id=dob.entity_type_id', 'store_id')
                    ->where('dob.entity_type_id=?', $customerEntityTypeID)
                    ->where('dob.attribute_id=?', $customerDateOfBirthAttributeId)
                    ->where('DATE_FORMAT(dob.value, "%m-%d")=?', date('m-d', $time - (int)($chain['BEFORE']*$chain['DAYS']*86400)));

                $birthDays = $read->fetchAll($select);

                if(count($birthDays))
                {
                    $params = array();
                    foreach($birthDays as $birthDay)
                    {
                        $params['object_id'] = $birthDay['entity_id'];
                        $params['customer_id'] = $birthDay['entity_id'];
                        $params['dob'] = $birthDay['value'];
                        $params['store_id'] = $birthDay['store_id'];

                        AWW_Followupemail_Model_Log::log('customer birthday event processing, rule_id='.$ruleId.', customerId='.$params['customer_id'].', dob='.$params['dob'].', store_id='.$params['store_id']);

                        $rule->processBirthday($params, $chain['TEMPLATE_ID'], $chain['BEFORE']*$chain['DAYS'], $sequenceNumber);
                    }
                }
                $sequenceNumber++;
            }
        }
    }

}