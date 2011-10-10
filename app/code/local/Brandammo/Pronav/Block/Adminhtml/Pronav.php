<?php
class Brandammo_Pronav_Block_Adminhtml_Pronav extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_pronav';
    $this->_blockGroup = 'pronav';
    $this->_headerText = Mage::helper('pronav')->__('ProNav Items Manager');
    $this->_addButtonLabel = Mage::helper('pronav')->__('Add ProNav Item');
    parent::__construct();
  }
}