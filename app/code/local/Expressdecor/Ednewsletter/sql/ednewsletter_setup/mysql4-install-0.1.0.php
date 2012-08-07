<?php     
/**
 *  Expressdecor Newsletter 
 *  @version 1.1.0
 *  @author Alex
 *  @var unknown_type
 *  @copyright 2012
 */ 
$installer = $this;
$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('newsletter_subscriber'), 'source', 'varchar(255)');
$installer->endSetup();
?>