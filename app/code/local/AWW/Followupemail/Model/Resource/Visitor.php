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


class AWW_Followupemail_Model_Resource_Visitor extends Mage_Log_Model_Resource_Visitor
{

    /**
     * Saving information about customer
     *
     * @param   Mage_Log_Model_Visitor $visitor
     * @return  Mage_Log_Model_Resource_Visitor
     */
    protected function _saveCustomerInfo($visitor)
    {
        $adapter = $this->_getWriteAdapter();

        if ($visitor->getDoCustomerLogout() && $logId = $visitor->getCustomerLogId()) {

            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_read');
            $select = new Zend_Db_Select($connection);
            $select->from($resource->getTableName('log/customer'));
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->columns('login_at');
            $select->where('log_id = ?', $logId);
            $loginAt = $connection->fetchOne($select);

            if (!$loginAt) {
                return parent::_saveCustomerInfo($visitor);
            }

            $data = new Varien_Object(array(
                'login_at' => $loginAt,
                'logout_at' => Mage::getSingleton('core/date')->gmtDate(),
                'store_id' => (int)Mage::app()->getStore()->getId(),
            ));

            $bind = $this->_prepareDataForTable($data, $this->getTable('log/customer'));

            $condition = array(
                'log_id = ?' => (int)$logId,
            );

            $adapter->update($this->getTable('log/customer'), $bind, $condition);

            $visitor->setDoCustomerLogout(false);
            $visitor->setCustomerId(null);
            $visitor->setCustomerLogId(null);
        } else {
            return parent::_saveCustomerInfo($visitor);
        }

        return $this;
    }

}