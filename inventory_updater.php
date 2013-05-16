<?php 
ini_set ( "memory_limit", "2048M" );
set_time_limit ( 0 );
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'app/Mage.php';
Mage::app ( 'default' );

$helper=Mage::Helper('inventoryupload');
$helper->scanfolder();