<?php

class Mage_Banners_Block_Adminhtml_Banners_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('banners_form', array('legend'=>Mage::helper('banners')->__('Item information')));
     
	  $object = Mage::getModel('banners/banners')->load( $this->getRequest()->getParam('id') );
	  $imgPath = Mage::getBaseUrl('media')."Banners/images/thumb/".$object['bannerimage'];
	 
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('banners')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('bannerimage', 'file', array(
          'label'     => Mage::helper('banners')->__('Banner Image'),
          'required'  => false,
          'name'      => 'bannerimage',
	  ));
	  
	  if( $object->getId() ){
		  $tempArray = array(
				  'name'      => 'filethumbnail',
				  'style'     => 'display:none;',
			  );
		  $fieldset->addField($imgPath, 'thumbnail',$tempArray);
	  }
	  
	  $fieldset->addField('link', 'text', array(
          'label'     => Mage::helper('banners')->__('Link'),
          'required'  => false,
          'name'      => 'link',
	  ));
	  
	 $fieldset->addField('target', 'select', array(
          'label'     => Mage::helper('banners')->__('Target'),
          'name'      => 'target',
          'values'    => array(
              array(
                  'value'     => '_blank',
                  'label'     => Mage::helper('banners')->__('Open in new window'),
              ),

              array(
                  'value'     => '_self',
                  'label'     => Mage::helper('banners')->__('Open in same window'),
              ),
          ),
      ));
	 
	  $fieldset->addField('sort_order', 'text', array(
          'label'     => Mage::helper('banners')->__('Sort Order'),
          'required'  => false,
          'name'      => 'sort_order',
      ));

	 
	 $fieldset->addField('textblend', 'select', array(
          'label'     => Mage::helper('banners')->__('Text Blend ?'),
          'name'      => 'textblend',
          'values'    => array(
              array(
                  'value'     => 'yes',
                  'label'     => Mage::helper('banners')->__('Yes'),
              ),

              array(
                  'value'     => 'no',
                  'label'     => Mage::helper('banners')->__('No'),
              ),
          ),
      ));
	
	 $fieldset->addField('store_id','multiselect',array(
			'name'      => 'stores[]',
            'label'     => Mage::helper('banners')->__('Store View'),
            'title'     => Mage::helper('banners')->__('Store View'),
            'required'  => true,
			'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
		));
	
	
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('banners')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('banners')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('banners')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('banners')->__('Content'),
          'title'     => Mage::helper('banners')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getBannersData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBannersData());
          Mage::getSingleton('adminhtml/session')->setBannersData(null);
      } elseif ( Mage::registry('banners_data') ) {
          $form->setValues(Mage::registry('banners_data')->getData());
      }
      return parent::_prepareForm();
  }
}