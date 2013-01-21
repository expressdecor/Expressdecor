<?php
class Expressdecor_Sgrid_Block_Sales_Order_Tab_Vendor_Form extends Mage_Adminhtml_Block_Widget_Form_Container {
	
	public function __construct() {  
		
		$id = (int) $this->getRequest()->getParam('order_id');
		parent::__construct ();
		$this->_objectId = 'id';
		$this->_blockGroup = 'sgrid';
		$this->_controller = 'sales_order_tab_vendor';		
		$this->_headerText = Mage::helper ( 'sgrid' )->__ ( 'Upload New Vendor Invoice' );
		$this->_mode = 'form';		
		$this->_updateButton('back','onclick', 'setLocation(\'' . $this->getUrl('adminhtml/sales_order/view',array('order_id'=>$id)) . '\');');
		$this->_removeButton('save');	
		$this->_removeButton('reset');
	}
	
	 

 
}