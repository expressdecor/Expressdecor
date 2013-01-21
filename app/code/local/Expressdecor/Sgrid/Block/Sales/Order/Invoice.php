<?php
class Expressdecor_Sgrid_Block_Sales_Order_Invoice extends Mage_Adminhtml_Block_Sales_Order_Invoice_View {
	
	public function __construct()
	{
		parent::__construct();
	/* 	if ($this->getInvoice()->getId()) {
			$this->_addButton('print_vendor', array(
					'label'     => Mage::helper('sales')->__('Print for Vendor'),
					'class'     => 'save',
					'onclick'   => 'setLocation(\''.$this->getPrintVendorUrl().'\')'
			)
			);
		} */
		
		if ($this->getInvoice()->getId()) {
			$this->_addButton('print_vendor_products', array(
					'label'     => Mage::helper('sales')->__('Print for Vendor prodcuts'),
					'class'     => 'save',
					'onclick'   => 'window.open(\''.$this->getPrintVendorUrlProducts().'\',\'Product chooser\',\'width=1300,height=800,scrollbars=yes\')'
			)
			);
		}	
			
 
	}
	
/* 	public function getPrintVendorUrl(){		 
		return $this->getUrl('sgrid/invoice/print', array(
				'order_id'  => $this->getInvoice()->getOrder()->getId(),
				'invoice_id' => $this->getInvoice()->getId()				 
		));
	}
	 */
	public function getPrintVendorUrlProducts(){
		return $this->getUrl('sgrid/invoice/chooseproducts', array(
				'order_id'  => $this->getInvoice()->getOrder()->getId(),
				'invoice_id' => $this->getInvoice()->getId()
		));
	}
}