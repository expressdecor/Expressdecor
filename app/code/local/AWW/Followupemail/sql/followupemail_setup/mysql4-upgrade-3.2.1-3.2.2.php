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


$installer = $this;
$installer->startSetup();

/**
 * Changing field type to TEXT for increase capacity
 */
$this->getConnection()
    ->changeColumn($this->getTable('followupemail/queue'), 'params', 'params', 'TEXT');

/**
 * If sender_email field in first row of collection contains not valid email
 * address - columns `sender_name` and `sender_email` will be interchanged
 * #2648
 */
$_emailValidator = new Zend_Validate_EmailAddress();
$_firstEmail = Mage::getModel('followupemail/queue')->getCollection()
    ->setPageSize(1)
    ->setCurPage(1)
    ->load()
    ->getData();
if(!isset($_firstEmail[0]['sender_email']) || !$_emailValidator->isValid($_firstEmail[0]['sender_email'])) {
    $this->getConnection()
        ->changeColumn($this->getTable('followupemail/queue'), 'sender_name', 'sender_name2', 'VARCHAR(255)');
    $this->getConnection()
        ->changeColumn($this->getTable('followupemail/queue'), 'sender_email', 'sender_name', 'VARCHAR(255)');
    $this->getConnection()
        ->changeColumn($this->getTable('followupemail/queue'), 'sender_name2', 'sender_email', 'VARCHAR(255)');
}

/**
 * Adding column into rules table
 */
$this->getConnection()
    ->addColumn($this->getTable('followupemail/rule'), 'email_send_to_customer',
        "TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `email_copy_to`");

$installer->endSetup();
