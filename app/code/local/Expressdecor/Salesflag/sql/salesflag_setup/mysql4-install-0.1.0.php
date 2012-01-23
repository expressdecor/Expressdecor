<?php     

$installer = $this;

$installer->startSetup();
 
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order'), 'sales_flag', 'varchar(255)');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order'), 'channel', 'varchar(255)');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_grid'), 'sales_flag', 'varchar(255)');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_order_grid'), 'channel', 'varchar(255)');

$installer->endSetup();


?>