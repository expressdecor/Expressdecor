<?php     

$installer = $this;

$installer->startSetup();
 
$installer->getConnection()->addColumn($installer->getTable('eav_attribute'), 'attribute_set_views', 'text');
$installer->endSetup();


?>