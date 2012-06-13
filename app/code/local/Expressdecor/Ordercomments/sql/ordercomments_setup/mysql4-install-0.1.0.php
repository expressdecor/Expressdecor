<?php
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_status_history'), 'track_user', 'varchar(255)');
$installer->endSetup();
?>