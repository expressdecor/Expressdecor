<?php
class Expressdecor_Sgrid_Model_Mysql4_Sgrid extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{		
		$this->_init('sgrid/sgrid','vendor_invoice_id');
	}
	
 
	public function loadByOrderId($order_id)
	{
		$adapter = $this->_getReadAdapter();
	
		$select = $adapter->select()
		->from('expresdecor_sgrid_invoice', '*')
		->where('product_id ='.$order_id);	
		  	
		return $adapter->fetchRow($select);
	}
}