<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@expressdecor.com so we can send you a copy immediately.
 *
 * @author Alex Lukyanov
 * @copyright   Copyright (c) 2013 ExpressDecor. (http://www.expressdecor.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Created: May 7, 2013
 *
 */
class Expressdecor_Inventoryupload_Block_Inventoryupload_Form extends Mage_Adminhtml_Block_Widget_Form{

 
	/* (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
	 */
	protected function _prepareForm(){
		 
        $form = new Varien_Data_Form(array(
            'id'        => 'inventoryupload_form',
            'action'    => $this->getUrl('*/*/save'),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));
        
        $fieldset = $form->addFieldset('inventory_upload_form', array(
        		'legend' =>Mage::helper('inventoryupload')->__('Inventory File')
        ));
        
         $fieldset->addField('filecsv', 'file', array(
        		'label'     => Mage::helper('inventoryupload')->__('Upload'),
        		'value'  => 'Upload',
        		'disabled' => false,
        		'readonly' => true,
         		'name'=>'filecsv', 
         		'required'=>true,
        		'after_element_html' => '<small>Please select .csv inventory file</small>',
        		'tabindex' => 1
        )); 
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
	
}