<?php
class Expressdecor_Sgrid_Block_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {
	
	public function __construct()
	{
		parent::__construct();
		if ($this->getOrder()->getId()) {
			$this->_addButton('add_invoice', array(
					'label'     => Mage::helper('sgrid')->__('Add Vendor Invoice'),
					'class'     => 'go',
					'onclick'   => "setLocation('".$this->getUrl('sgrid/invoice/newinvoice', array('order_id'  => $this->getOrder()->getId()) )."')",
			));
		}
 	
	}
	
 

}