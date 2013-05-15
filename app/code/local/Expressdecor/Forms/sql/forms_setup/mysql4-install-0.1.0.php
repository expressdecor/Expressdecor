<?php     

$installer = $this;

$installer->startSetup();
 
$table = $installer->getConnection()->newTable($installer->getTable('forms/giveaway'))
->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
), 'Item ID')
->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => false,
), 'Customer Name')
->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
		'nullable' => true,
), 'Customer Email')
->addColumn('date_created', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
), 'Timestamp')
->setComment('Expressdecor Giveaway From Data');
$installer->getConnection()->createTable($table);


$installer->endSetup();


?>