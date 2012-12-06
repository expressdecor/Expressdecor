<?php
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('expressdecor_facebook_customer')} ( 
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, 
  `customer_id` int(10) UNSIGNED NOT NULL,
  `store_id` int(10) NOT NULL,
  `website_id` int(10) NOT NULL,
  `fb_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `FB_CUSTOMER` (`customer_id`,`fb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{$this->getTable('expressdecor_facebook_customer')}`
ADD FOREIGN KEY ( `customer_id` ) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ;
");
$installer->endSetup();