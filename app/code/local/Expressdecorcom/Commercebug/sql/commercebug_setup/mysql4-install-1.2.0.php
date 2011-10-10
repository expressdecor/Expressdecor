<?php
	$installer = $this;
	$installer->startSetup();
	$installer->run("
		CREATE TABLE `{$installer->getTable('commercebug/snapshot')}` (
		  `snapshot_id` int(11) NOT NULL auto_increment,
		  `snapshot_name_id` int not null,
		  `hash` varchar(32),
		  `file` text,
		  `contents` mediumtext,
		  PRIMARY KEY  (`snapshot_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");
	
	$installer->run("
		CREATE TABLE `{$installer->getTable('commercebug/snapshot_name')}` (
		  `snapshot_name_id` int(11) NOT NULL auto_increment,
		  `snapshot_name` varchar(255) NOT NULL,
		  PRIMARY KEY  (`snapshot_name_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");
	
	$installer->endSetup();