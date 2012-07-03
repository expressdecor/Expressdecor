<?php
class Expressdecor_Orderreport_Block_Adminhtml_Orderreport extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {
		$this->_controller = 'adminhtml_orderreport';
		$this->_blockGroup = 'orderreport';
		$this->_headerText = Mage::helper('orderreport')->__('Expressdecor Orders Report');
		parent::__construct();		
		$this->_removeButton('add');
		 
	}

}