<?php
class Expressdecor_Facebook_Model_Facebook extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('facebook/facebook');
	}
}