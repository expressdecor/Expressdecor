<?php

class Brandammo_Pronav_Block_Adminhtml_Pronav_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('pronav_form', array('legend'=>Mage::helper('pronav')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('pronav')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('pronav')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('pronav')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('pronav')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('pronav')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('pronav')->__('Content'),
          'title'     => Mage::helper('pronav')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getPronavData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPronavData());
          Mage::getSingleton('adminhtml/session')->setPronavData(null);
      } elseif ( Mage::registry('pronav_data') ) {
          $form->setValues(Mage::registry('pronav_data')->getData());
      }
      return parent::_prepareForm();
  }
}