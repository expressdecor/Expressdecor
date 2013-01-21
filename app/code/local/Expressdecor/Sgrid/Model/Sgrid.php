<?php
class Expressdecor_Sgrid_Model_Sgrid extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('sgrid/sgrid');
	}
	
	public function loadByOrderId($order_id)
	{
		$data= $this->_getResource()->loadByOrderId($order_id);
		$this->setData($data);
		return $this;
	}
	
	
}