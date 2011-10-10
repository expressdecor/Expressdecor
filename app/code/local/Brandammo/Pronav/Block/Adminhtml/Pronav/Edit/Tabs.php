<?php

class Brandammo_Pronav_Block_Adminhtml_Pronav_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('pronav_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('pronav')->__('ProNav Item'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('pronav')->__('Item Data'),
          'title'     => Mage::helper('pronav')->__('ProNav Item'),
          'content'   => $this->getLayout()->createBlock('pronav/adminhtml_pronav_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}