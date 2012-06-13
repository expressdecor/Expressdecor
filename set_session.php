<?php
require_once 'app/Mage.php';
Mage::run('','store');

Mage::getSingleton("customer/session", array("name" => "frontend"));
$open=Mage::app()->getRequest()->getParam('customer_show');
Mage::getModel('customer/session')->setNeedHelp($open);

?>
