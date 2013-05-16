<?php 
ini_set ( "memory_limit", "2048M" );
set_time_limit ( 0 );

require_once 'app/Mage.php';
Mage::app ( 'default' );

$helper=Mage::Helper('inventoryupload');
$helper->scanfolder();