<?php
/**
 * Scalena_News extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Scalena
 * @package    Scalena_News
 * @copyright  Copyright (c) 2009 Scalena Agency SA
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Scalena
 * @package    Scalena_News
 * @author     Anthony Charrex <anthony@scalena.com>
 */

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('banners_store')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('banners_store')}` (
  `banners_id` int(11) NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY  (`banners_id`,`store_id`),
  KEY `FK_BANNERS_STORE_STORE` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Usol Banners Stores';
");

$connection->addConstraint('FK_BANNERS_STORE_STORE',
    $installer->getTable('banners_store'), 'store_id',
    $installer->getTable('core_store'), 'store_id',
    'CASCADE', 'CASCADE', true
);

$connection->addConstraint('banners_store_ibfk_1',
    $installer->getTable('banners_store'), 'banners_id',
    $installer->getTable('banners'), 'banners_id',
    'CASCADE', 'CASCADE', true
);


$installer->run("
ALTER TABLE {$this->getTable('banners/banners')}
	Add column `sort_order` int(11) default '0' NULL after `content` ;
");

$installer->endSetup(); 

?>


