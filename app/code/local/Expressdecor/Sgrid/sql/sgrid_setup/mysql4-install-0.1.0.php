<?php
$installer = $this;

$installer->startSetup();

$installer->run("
		CREATE TABLE IF NOT EXISTS  {$this->getTable('expressdecor_sgrid_invoice')} (
		`vendor_invoice_id` int(11) unsigned NOT NULL auto_increment,
		`order_id`  int(11) NOT NULL,
		`filename` varchar(255) NOT NULL,	 
		`update_time` TIMESTAMP  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (`vendor_invoice_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
		 

$installer->endSetup();