<?php
class Expressdecor_Sgrid_Block_Sales_Order_Product_Product extends Mage_Adminhtml_Block_Widget_Form_Container {

	public function __construct() {

		$order_id = (int) $this->getRequest()->getParam('order_id');
		$invoice_id=(int) $this->getRequest()->getParam('invoice_id');
		parent::__construct ();
		$this->_objectId = 'id';
		$this->_blockGroup = 'sgrid';
		$this->_controller = 'sales_order_product';
		$this->_headerText = Mage::helper ( 'sgrid' )->__ ( 'Choose products to print' );
		$this->_mode = 'form';
		$this->_removeButton('back');
		$this->_removeButton('save');		
		$this->_removeButton('reset');
		
	  	if ($invoice_id) {
			$this->_addButton('print_vendor', array(
					'label'     => Mage::helper('sales')->__('Print for Vendor'),
					'class'     => 'save',					
					'onclick'   => 'product_choser_form.submit();'
			)
			);
		}  
		
		$this->_addButton('close', array(
				'label'     => Mage::helper('sales')->__('Close'),
				'class'     => 'close',
				'onclick'   => 'window.close()'
		)
		);
	}


	  public function getPrintVendorUrl(){
		return $this->getUrl('sgrid/invoice/print', array(
				'order_id'  => $this->getRequest()->getParam('order_id'),
				'invoice_id' => $this->getRequest()->getParam('invoice_id')
		));
	}  

}