<?php     

$installer = $this;

$installer->startSetup();
 
$installer->getConnection()->addColumn($installer->getTable('tag_relation'), 'position', 'int(11) DEFAULT 0');
$installer->endSetup();


?>