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
try
{
    $installer->run("
    ALTER TABLE {$this->getTable('followupemail/rule')}
    ADD `ga_source` VARCHAR( 100 ) NOT NULL COMMENT 'Google Analytics Source',
    ADD `ga_medium` VARCHAR( 100 ) NOT NULL COMMENT 'Google Analytics Medium',
    ADD `ga_term` VARCHAR( 100 ) NOT NULL COMMENT 'Google Analytics Term',
    ADD `ga_content` VARCHAR( 100 ) NOT NULL COMMENT 'Google Analytics Content',
    ADD `ga_name` VARCHAR( 100 ) NOT NULL COMMENT 'Google Analytics Name'
    ");
} catch(Exception $e) { Mage::logException($e); }
$installer->endSetup();

/**
 * Adding new template for "Customer New Subscription" event
 */
$templateResource = Mage::getResourceModel('newsletter/template');
$modelTemplate = Mage::getModel('newsletter/template');
$templateResource->loadByCode($modelTemplate, 'AW Customer New Subscription');
if($modelTemplate->getData() == array()) {
    $template = array(
        'template_code' => 'AW Customer New Subscription',
        'template_subject' => 'New Subscription',
        'template_sender_name' => 'AW',
        'template_sender_email' => 'aw@example.com',
        'template_text' => '<p>Dear {{var customer_name}}!</p>
    <p>Thank you for your subscription!</p>
    {{depend has_coupon}}<p>Your coupon code is: {{var coupon.code}}, expires at {{var coupon.expiration_date}}</p>
    <p>Your coupon code is: {{var coupons.1.code}}, expires at {{var coupons.1.expiration_date}}</p>
    <p>Your coupon code is: {{var coupons.2.code}}, expires at {{var coupons.2.expiration_date}}</p>
    <p>Your coupon code is: {{var coupons.test.code}}, expires at {{var coupons.test.expiration_date}}</p>
    <p>Your coupon code is: {{var coupons.test_alias.code}}, expires at {{var coupons.test_alias.expiration_date}}</p>
    {{/depend}}'
    );

    $modelTemplate->setData($template)
        ->setTemplateType(Mage_Newsletter_Model_Template::TYPE_HTML)
        ->setTemplateActual(1)
        ->save();
}

