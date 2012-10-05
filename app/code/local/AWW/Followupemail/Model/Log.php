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


class AWW_Followupemail_Model_Log
{
    const LOG_FILE_NAME = 'followupemail_cron.log';
    const LOG_DIR = '/log';

    const TIME_MESSAGE_SEPARATOR        = ' ';

    const LOG_MESSAGE_PREFIX_SUCCESS    = 'SUCCESS: ';
    const LOG_MESSAGE_PREFIX_ERROR      = 'ERROR:   ';
    const LOG_MESSAGE_PREFIX_WARNING    = 'WARNING: ';
    const LOG_MESSAGE_PREFIX_DEBUG      = 'DEBUG:   ';


    /*
     * Logs message
     * @param string $message Message to log
     * @param string $prefix Message prefix (to make formatting tabulation in log file)
     */
    public static function log($message, $prefix='         ', $severity = null, $object = null, $description = null, $line = null) {
        if(!self::_isLogEnabled()) return;
        
        if(is_null($object)) $object = Mage::getSingleton('followupemail/log');
        Mage::helper('awcore/logger')->log($object, $prefix.$message, $severity, $description, $line);
    }

    /*
     * Logs message with SUCCESS prefix
     * @param string $message Message to log
     */
    public static function logSuccess($message, $object = null)
    {
        self::log($message, self::LOG_MESSAGE_PREFIX_SUCCESS, AW_Core_Model_Logger::LOG_SEVERITY_NOTICE, $object);
    }

    /*
     * Logs message with ERROR prefix
     * @param string $message Message to log
     */
    public static function logError($message, $object = null)
    {
        self::log($message, self::LOG_MESSAGE_PREFIX_ERROR, AW_Core_Model_Logger::LOG_SEVERITY_ERROR, $object);
    }

    /*
     * Logs message with WARNING prefix
     * @param string $message Message to log
     */
    public static function logWarning($message, $object = null)
    {
        self::log($message, self::LOG_MESSAGE_PREFIX_WARNING, AW_Core_Model_Logger::LOG_SEVERITY_WARNING, $object);
    }

    /*
     * Logs message with DEBUG prefix
     * @param string $message Message to log
     */
    public static function debug($message, $object = null)
    {
        self::log($message, self::LOG_MESSAGE_PREFIX_DEBUG, AW_Core_Model_Logger::LOG_SEVERITY_STRICT_NOTICE, $object);
    }

    /*
     * Returns value of the "Log enabled" extension general configuration setting
     * @return string '0' or '1'
     */
    protected static function _isLogEnabled()
    {
        return Mage::getStoreConfig('followupemail/general/keeplog');
    }

}
