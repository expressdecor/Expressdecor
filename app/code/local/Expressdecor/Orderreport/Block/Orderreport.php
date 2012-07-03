<?php
class Expressdecor_Orderreport_Block_Orderreport extends Mage_Core_Block_Template {

	public function _prepareLayout() {		 
		return parent::_prepareLayout();
	}

	public function getMymodule() {
		if (!$this->hasData('orderreport')) {
			$this->setData('orderreport', Mage::registry('orderreport'));
		}
		return $this->getData('orderreport');
	}

}