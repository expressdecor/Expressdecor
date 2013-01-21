<?php
class Expressdecor_Sgrid_Block_Sales_Order_Product_Form_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	
	protected function _prepareForm()
	{
			
		$id = (int) $this->getRequest()->getParam('order_id');

		$form = new Varien_Data_Form(
		array(
		'id' => 'product_choser_form',
		'action' => $this->getUrl('sgrid/invoice/print', array(
				'order_id'  => $this->getRequest()->getParam('order_id'),
				'invoice_id' => $this->getRequest()->getParam('invoice_id')
		)),
		'method' => 'post',
		'enctype' => 'multipart/form-data'
		)
		);
	
		$form->setUseContainer(true);
		$this->setForm($form);
	
		$helper = Mage::helper('sgrid');
		 
		$fieldset = $form->addFieldset('display', array(
				'legend' => $helper->__('Please Choose Products to print'),
				'class' => 'fieldset-full',
		));
		
		$products=array();
		
		$order=Mage::getModel('sales/order')->load($id);
		 
		foreach ($order->getItemsCollection() as $Item_key=>$item) {
				$product=Mage::getModel('catalog/product')->load($item->getProductId());
				 array_push($products, array('value'=>$product->getId(),'label'=>$product->getName()));
		}
		 
 
		
		$fieldset->addField('checkboxes', 'checkboxes', array(
				'label'     => Mage::helper('sgrid')->__('Products'),
				'name'      => 'products[]',
				'values' =>$products,
				'onclick' => "",
				'onchange' => "",
				'value'  => '1',
				'disabled' => false,				
				'tabindex' => 1
		));
		
		
		return parent::_prepareForm();
	}
	
}