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


$installer = $this;
$installer->startSetup();

$lastExecTime = time();
$configTableName = $this->getTable('followupemail/config');
$oldConfig = $this->_conn->query('SELECT * FROM '.$configTableName)->fetchAll();

foreach($oldConfig as $v)
    if(isset($v['FIELD']) && 'LAST_EXECUTION_TIMESTAMP' == $v['FIELD'])
    {
        $lastExecTime = $v['VALUE'];
        break;
    }

$installer->run("
DROP TABLE IF EXISTS $configTableName;

CREATE TABLE $configTableName (
 `name` varchar(128) NOT NULL,
 `value` varchar(255) NOT NULL,
 PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO $configTableName VALUES('".AWW_Followupemail_Model_Config::LAST_EXEC_TIME."', '".$lastExecTime."');
INSERT INTO $configTableName VALUES('".AWW_Followupemail_Model_Config::LAST_EXEC_TIME_DAILY."', '".((int)$lastExecTime-3600)."');
INSERT INTO $configTableName VALUES('".AWW_Followupemail_Model_Config::EMAIL_SENDER_LAST_EXEC_TIME."', '".$lastExecTime."');
");


$ruleTableName = $this->getTable('followupemail/rule');
$oldRules = $this->_conn->query('SELECT * FROM '.$ruleTableName)->fetchAll();

$installer->run("
DROP TABLE IF EXISTS $ruleTableName;

CREATE TABLE $ruleTableName (
 `id` int(11) unsigned NOT NULL auto_increment,
 `is_active` tinyint(1) NOT NULL default '0',
 `event_type` varchar(128) NOT NULL,
 `title` varchar(255) NOT NULL,
 `chain` text NOT NULL,
 `store_ids` varchar(255) NOT NULL,
 `product_type_ids` varchar(128) NOT NULL default '',
 `category_ids` varchar(255) NOT NULL default '',
 `product_ids` varchar(255) NOT NULL default '',
 `sale_amount` varchar(255) default NULL,
 `email_copy_to` varchar(255) NOT NULL default '',
 `test_objects` text,
 `test_recipient` varchar(255) default '',
 `sender_name` varchar(255) default '',
 `sender_email` varchar(255) default '',
 `cancel_events` text,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");


// converting categories
$allCategories = Mage::getModel('catalog/category')
    ->getCollection()
    ->addAttributeToSelect('name')
    ->load()
    ->toArray();

$allCategoryIds = array();

foreach($allCategories as $categoryID => $category)
    if(isset($category['name'])) $allCategoryIds[] = $categoryID;



// converting rule event types
$ruleTypeConvert = array('abandoned_cart' => AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW);
foreach(Mage::getSingleton('sales/order_config')->getStatuses() as $code => $name)
    $ruleTypeConvert[$code] = AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ORDER_STATUS_PREFIX . $code;


$newsletterModel = Mage::getModel('core/email_template');

// main conversion
foreach($oldRules as $rule)
{
    $chain = unserialize($rule['CHAIN']);
    foreach($chain as $k => $v)
    {
        $newsletterModel->load($v['TEMPLATE_ID']);

        $chain[$k]['TEMPLATE_ID'] = AWW_Followupemail_Model_Source_Rule_Template::TEMPLATE_SOURCE_EMAIL
            .AWW_Followupemail_Model_Source_Rule_Template::TEMPLATE_SOURCE_SEPARATOR
            .$newsletterModel->getTemplateCode();
    }

    $ruleNewCategories = $allCategoryIds;
    foreach(unserialize($rule['CATEGORIES']) as $c)
        if(!$c)
        {
            $ruleNewCategories = array();
            break;
        }
        elseif($k = array_search($c, $ruleNewCategories)) unset($ruleNewCategories[$k]);

    $installer->run('INSERT INTO '.$ruleTableName.' SET '
        .'  is_active='.(int)$rule['ACTIVE']
        .', event_type="'.(isset($ruleTypeConvert[$rule['EVENT_TYPE']]) ? $ruleTypeConvert[$rule['EVENT_TYPE']] : 0).'"'
        .', title="'.mysql_escape_string($rule['TITLE']).'"'
        .', chain="'.mysql_escape_string(serialize($chain)).'"'
        .', store_ids="'.$rule['STORE_ID'].'"'
        .', product_type_ids="'.($rule['PRODUCT_TYPE']?$rule['PRODUCT_TYPE']:AWW_Followupemail_Model_Source_Product_Types::PRODUCT_TYPE_ALL).'"'
        .', category_ids="'.implode(',', $ruleNewCategories).'"'
    );
}

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('followupemail/queue')} (
 `id` int(11) unsigned NOT NULL auto_increment,
 `code` varchar(128) NOT NULL,
 `created_at` datetime NOT NULL,
 `scheduled_at` datetime NOT NULL,
 `sent_at` datetime default NULL,
 `sequence_number` smallint(5) unsigned NOT NULL default '1',
 `sender_name` varchar(255) default NULL,
 `sender_email` varchar(255) default NULL,
 `recipient_name` varchar(255) NOT NULL,
 `recipient_email` varchar(255) NOT NULL,
 `subject` varchar(255) default '',
 `content` text NOT NULL,
 `status` enum('R','S','F','C') NOT NULL default 'R' COMMENT 'Ready,Sent,Failed,Cancelled',
 `rule_id` int(11) unsigned NOT NULL default '0',
 `object_id` int(11) NOT NULL default '0',
 `params` varchar(255) default '',
 PRIMARY KEY (`id`),
 KEY `rule_id` (`rule_id`),
 CONSTRAINT `FK_queue_to_rule` FOREIGN KEY (`rule_id`) REFERENCES $ruleTableName (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS {$this->getTable('followupemail/linktracking')} (
 `id` int(11) unsigned NOT NULL auto_increment,
 `queue_id` int(11) unsigned NOT NULL,
 `visited_at` datetime NOT NULL,
 `visited_from` varchar(15) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `queue_id` (`queue_id`),
 CONSTRAINT `FK_link_to_queue` FOREIGN KEY (`queue_id`) REFERENCES {$this->getTable('followupemail/queue')} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$tablePrefix = '';
$coreConfig = Mage::getModel('core/config');
if($coreConfig instanceof Mage_Core_Model_Config)
try
{
    $tablePrefix = $coreConfig->getTablePrefix();
    $installer->run('DROP TABLE IF EXISTS '.$tablePrefix.'aw_followup_entry');
}
catch (Exception $e) { }

$installer->endSetup();


// template loader
require 'installtemplates.php';
