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
 * Adding new template for "Customer group changed" event
 */

$templateResource = Mage::getResourceModel('newsletter/template');
$modelTemplate = Mage::getModel('newsletter/template');
$templateResource->loadByCode($modelTemplate, 'AW Customer group changed');
if($modelTemplate->getData() == array()) {
    $template = array(
        'template_code' => 'AW Customer group changed',
        'template_subject' => 'Your group has been changed',
        'template_sender_name' => 'AW',
        'template_sender_email' => 'aw@example.com',
        'template_text' => '<p>Dear {{var customer_name}}!</p>
    <p>Your new group is {{var customer_new_group}}</p>
    {{depend has_coupon}}<p>Your coupon code is: {{var coupon.code}}, expires at {{var coupon.expiration_date}}</p>{{/depend}}'
    );

    $modelTemplate->setData($template)
        ->setTemplateType(Mage_Newsletter_Model_Template::TYPE_HTML)
        ->setTemplateActual(1)
        ->save();
}

$this->getConnection()
    ->addColumn($this->getTable('followupemail/rule'), 'coupon_enabled', "TINYINT( 1 ) NOT NULL default '0'");
$this->getConnection()
    ->addColumn($this->getTable('followupemail/rule'), 'coupon_sales_rule_id', "INT( 10 ) UNSIGNED NOT NULL");
$this->getConnection()
    ->addColumn($this->getTable('followupemail/rule'), 'coupon_prefix', "TINYTEXT NOT NULL");
$this->getConnection()
    ->addColumn($this->getTable('followupemail/rule'), 'coupon_expire_days', "INT( 10) UNSIGNED NOT NULL");

$installer->endSetup();
