<?php

class Brandammo_Pronav_Block_Adminhtml_Pronav_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('pronav_form', array('legend'=>Mage::helper('pronav')->__('Item information')));
     
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('pronav')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
      ));
	  
	  $fieldset->addField('url_key', 'text', array(
          'label'     => Mage::helper('pronav')->__('URL Key'),
          'name'      => 'url_key',
	  ));
	  
	  
	  $fieldset->addField('index', 'text', array(
          'label'     => Mage::helper('pronav')->__('Item Index'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'index',
	  ));
	  
	  $fieldset->addField('li_css_id', 'text', array(
          'label'     => Mage::helper('pronav')->__('Item CSS ID'),
          'name'      => 'li_css_id',
	  ));
	  
	  $fieldset->addField('li_css_class', 'text', array(
          'label'     => Mage::helper('pronav')->__('Item CSS Class'),
          'name'      => 'li_css_class',
	  ));
	  
	  $fieldset->addField('css_id', 'text', array(
          'label'     => Mage::helper('pronav')->__('Link CSS ID'),
          'name'      => 'css_id',
	  ));
	  
	  $fieldset->addField('css_class', 'text', array(
          'label'     => Mage::helper('pronav')->__('Link CSS Class'),
          'name'      => 'css_class',
	  ));
	  
	  $staticBlockCollection = Mage::getResourceModel('cms/block_collection')
                ->load()
                ->toOptionArray();
            
     $matchedStaticBlocks = array();
     
     foreach ($staticBlockCollection as $staticBlock) {
          $label = trim(strtoupper($staticBlock['label']));
     	    if (preg_match('%^PRONAV%', $label))
     	    {
     	       $matchedStaticBlocks[] = $staticBlock;
     	    }
     }
     array_unshift($matchedStaticBlocks, array('value'=>'', 'label'=>Mage::helper('catalog')->__('Please select a static block ...')));
     
	  $fieldset->addField('static_block', 'select', array(
          'label'     => Mage::helper('pronav')->__('Static Block'),
          'name'      => 'static_block',
	       'values'    => $matchedStaticBlocks
	  ));
	  
     if (!Mage::app()->isSingleStoreMode()) {
      $fieldset->addField('store_id', 'select', array(
           'name'      => 'store_id',
           'label'     => Mage::helper('pronav')->__('Store View'),
           'title'     => Mage::helper('pronav')->__('Store View'),
           'required'  => true,
           'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
      ));
     }
		
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