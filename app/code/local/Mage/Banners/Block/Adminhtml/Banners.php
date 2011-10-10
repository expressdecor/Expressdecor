<?php
class Mage_Banners_Block_Adminhtml_Banners extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_banners';
    $this->_blockGroup = 'banners';
    $this->_headerText = Mage::helper('banners')->__('Banners Manager');
    $this->_addButtonLabel = Mage::helper('banners')->__('Add Banner');
	
    parent::__construct();
  }
}