<?php
class Expressdecor_Sgrid_Block_Sales_Order_Tab_Vendor_Form_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	
	protected function _prepareForm()
	{
			
		$id = (int) $this->getRequest()->getParam('order_id');

		$form = new Varien_Data_Form(
		array(
		'id' => 'upload_form',
		'action' => $this->getUrl('sgrid/invoice/save', array('order_id' => $this->getRequest()->getParam('order_id'))),
		'method' => 'post',
		'enctype' => 'multipart/form-data'
		)
		);
	
		$form->setUseContainer(true);
		$this->setForm($form);
	
		$helper = Mage::helper('sgrid');
		 
		$fieldset = $form->addFieldset('display', array(
				'legend' => $helper->__('Please Choose Invoice and Click Upload'),
				'class' => 'fieldset-full',
		));
		
		return parent::_prepareForm();
	}
	
}