<?php 

class Expressdecor_Shipp_Block_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals
{
	/**
	 * Initialize order totals array
	 *
	 * @return Mage_Sales_Block_Order_Totals
	 */
	protected function _initTotals()
	{
		parent::_initTotals();
		$signature_enabled=Mage::getStoreConfig('sales/signature_shipping/enabled');
		$signature_value=$this->getSource()->getShippingAddress()->getSignatureRequired();
		 
		/**
		 * Add shipping signature
		 */ 	 
		if ($signature_enabled && $signature_value && !$this->getSource()->getIsVirtual() )
		{
			
			$this->_totals['signature'] = new Varien_Object(array(
					'code'      => 'signature',
					'value'     => $signature_value,
					'base_value'=> $signature_value,
					'label' => Mage::helper('shipp')->__('Signature at delivery')
			));
		}
		 
		 return $this;
	}
}