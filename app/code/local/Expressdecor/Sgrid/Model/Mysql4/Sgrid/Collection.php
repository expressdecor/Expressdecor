<?php
class Expressdecor_Sgrid_Model_Mysql4_Sgrid_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		$this->_init('sgrid/sgrid');
	}
	
	public function AddOrderFilter($order_id){
		$this->addFieldToFilter('order_id',array('eq'=>$order_id));
				 
		return $this;
	}
}