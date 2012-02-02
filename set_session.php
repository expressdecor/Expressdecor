<?php
require_once 'app/Mage.php';
Mage::app('default');

Mage::getSingleton("core/session", array("name" => "frontend"));
$open=Mage::app()->getRequest()->getParam('customer_show');
Mage::getModel('core/session')->setNeedHelp($open);

?>