<?php
class Expressdecor_Sgrid_Block_Sales_Order_Tab_Vendor extends Mage_Adminhtml_Block_Widget_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	protected $_chat = null;
	
	protected function _construct()
	{		 
		parent::_construct();
		$this->setUseAjax(true);		 		 
	}
	
	public function getTabLabel() {
		return $this->__('Vendor Invoice');
	}
	
	public function getTabTitle() {
		return $this->__('Vendor Invoice');
	}
	
	public function canShowTab() {
		return true;
	}
	
	public function isHidden() {
		return false;
	}
	 	
	public function getOrder(){
		return Mage::registry('current_order');
	}

	protected function _getCollectionClass()
	{
		return 'sgrid/sgrid_collection';
	}
	
	public function getGridUrl()
	{
		return $this->getUrl('sgrid/invoice/getinvoicestab', array('_current' => true));
	}
	
	public function getTabUrl(){
		return  $this->getUrl('sgrid/invoice/getinvoicestab', array('_current'=>true));
	}
	
 	protected function _prepareCollection()
	{
		$orderId = $this->getRequest()->getParam('order_id');
	 	$collection = Mage::getResourceModel($this->_getCollectionClass())
		->AddOrderFilter($orderId);
		$this->setCollection($collection);  
		return parent::_prepareCollection();
	} 
	
	public function getTabClass(){
		return 'ajax vendor-invoice-tab notloaded';
	}
	 
	protected function _prepareColumns()
	{
		$this->addColumn('vendor_invoice_id', array(
				'header' => Mage::helper('sales')->__('Vendoe Invoice #'),
				'index' => 'vendor_invoice_id',
		));
	
		$this->addColumn('filename', array(
				'header' => Mage::helper('sales')->__('Filename'),
				'index' => 'filename',
		));
	
		$this->addColumn('updated_time', array(
				'header' => Mage::helper('sales')->__('Date Uploaded'),
				'index' => 'updated_time',
				'type' => 'datetime',
		));
	
		return parent::_prepareColumns();
	}
	
	public function getRowUrl($row)
	{
		return $this->getUrl(
				'sgrid/invoice/download',
				array( 'id'=> $row->getVendorInvoiceId())
				);
	}
	
	
}