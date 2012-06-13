<?php
class Expressdecor_Adminhtml_Block_Page extends Mage_Adminhtml_Block_Template
    {
        public function __construct()
        {
            Mage::getDesign()->setTheme('expressdecor');
        }
    }