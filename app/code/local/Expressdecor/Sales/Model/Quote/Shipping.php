<?php
class Expressdecor_Sales_Model_Quote_Shipping extends Mage_Sales_Model_Quote_Address_Total_Shipping
{

	/**
	 * Add shipping totals information to address object
	 *
	 * @param   Mage_Sales_Model_Quote_Address $address
	 * @return  Mage_Sales_Model_Quote_Address_Total_Shipping
	 */
	public function fetch(Mage_Sales_Model_Quote_Address $address)
	{
		$amount = $address->getShippingAmount();
		if ($amount != 0 || $address->getShippingDescription()) {
			$title = Mage::helper('sales')->__('Shipping & Handling');
			if ($address->getShippingDescription()) {
				$title .= '' . $address->getShippingDescription() . '';
			}
			$address->addTotal(array(
					'code' => $this->getCode(),
					'title' => $title,
					'value' => $address->getShippingAmount()
			));
		}
		return $this;
	}
	
}