<?php 
$installer = $this;

$installer->startSetup();
 
$table = $installer->getConnection()->newTable($installer->getTable('inventoryupload/productposition'))
->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => false,
		'identity' => false,
), 'product_id')
->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'name')
->addColumn('total_ordered', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false,
), 'Customer Email')
->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable' => false,
), 'position')
->setComment('Expressdecor Products Positions');
$installer->getConnection()->createTable($table);


$installer->endSetup();