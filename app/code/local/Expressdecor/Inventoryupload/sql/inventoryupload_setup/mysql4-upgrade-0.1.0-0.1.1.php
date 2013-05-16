<?php 
$installer = $this;
 
$installer->getConnection()
    ->addColumn($installer->getTable('cataloginventory/stock_item'), 'stock_message', 'VARCHAR(255)'); 
 
 