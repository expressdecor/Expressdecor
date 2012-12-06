<?php
class Expressdecor_Facebook_Model_Mysql4_Facebook extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		$this->_init('facebook/facebook', 'id');
	}
}