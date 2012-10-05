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

$configTableName = $this->getTable('followupemail/config');

try
{
    $installer->run("

CREATE TABLE IF NOT EXISTS $configTableName (
 `name` varchar(128) NOT NULL,
 `value` varchar(255) NOT NULL,
 PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT IGNORE INTO $configTableName VALUES('".AWW_Followupemail_Model_Config::LAST_EXEC_TIME."', '".time()."');
INSERT IGNORE INTO $configTableName VALUES('".AWW_Followupemail_Model_Config::LAST_EXEC_TIME_DAILY."', '".(time()-3600)."');
INSERT IGNORE INTO $configTableName VALUES('".AWW_Followupemail_Model_Config::EMAIL_SENDER_LAST_EXEC_TIME."', '".time()."');


CREATE TABLE IF NOT EXISTS {$this->getTable('followupemail/rule')} (
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
 `sku` varchar(255) default NULL,
 `send_to_subscribers_only` tinyint(1) NOT NULL default '0',
 `customer_groups` varchar(255) default NULL,
 `anl_segments` text COMMENT 'Advanced Newsletter segments',
 `mss_rule_id` int(10) NOT NULL default '0' COMMENT 'aheadWorks Market Segmentation Suite rule ID',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


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
 CONSTRAINT `FK_queue_to_rule` FOREIGN KEY (`rule_id`) REFERENCES {$this->getTable('followupemail/rule')} (`id`) ON DELETE CASCADE
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

} catch(Exception $e) { Mage::logException($e); }

$installer->endSetup();

// template loader
require 'installtemplates.php';
