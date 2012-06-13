<?php

include_once('Mage/Adminhtml/controllers/Sales/Order/CreateController.php');

class Blc_Adminhtml_Sales_Order_CreateController extends Mage_Adminhtml_Sales_Order_CreateController
{
    
    public function preDispatch()
    {
        parent::preDispatch();
        // override admin store design settings via stores section
        Mage::getDesign()
            ->setArea($this->_currentArea)
            ->setPackageName((string)Mage::getConfig()->getNode('stores/admin/design/package/name'))
            ->setTheme('blc')
        ;
    }
    
}