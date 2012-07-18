<?php
class Expressdecor_Paypal_Model_Express_Checkout extends Mage_Paypal_Model_Express_Checkout {
	
	
	/**
	 * Make sure addresses will be saved without validation errors
	 */
	private function _ignoreAddressValidation()
	{
		$this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
		if (!$this->_quote->getIsVirtual()) {
			$this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
			if (!$this->_config->requireBillingAddress && !$this->_quote->getBillingAddress()->getEmail()) {
				$this->_quote->getBillingAddress()->setSameAsBilling(1);
			}
		}
	}
	
	/*
	 * Set shipping method to quote, if needed @param string $methodCode
	 */	
	public function updateShippingMethod($methodCode) {
		if (! $this->_quote->getIsVirtual () && $shippingAddress = $this->_quote->getShippingAddress ()) {
			if ($methodCode != $shippingAddress->getShippingMethod ()) {
				$this->_ignoreAddressValidation ();
				$shippingAddress->setShippingMethod ( $methodCode )->setCollectShippingRates ( true )->save(); // Alex
				$this->_quote->collectTotals ();
			}
		}
	}
}