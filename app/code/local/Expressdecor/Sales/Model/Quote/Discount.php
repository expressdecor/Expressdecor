<?php
class Expressdecor_Sales_Model_Quote_Discount extends Mage_SalesRule_Model_Quote_Discount
{


/**
 * Add discount total information to address
 *
 * @param   Mage_Sales_Model_Quote_Address $address
 * @return  Mage_SalesRule_Model_Quote_Discount
 */
public function fetch(Mage_Sales_Model_Quote_Address $address)
{
	$amount = $address->getDiscountAmount();

	if ($amount!=0) {
		$description = $address->getDiscountDescription();
		if (strlen($description)) {
			$title = Mage::helper('sales')->__('Coupons <br> (%s)', $description);
		} else {
			$title = Mage::helper('sales')->__('Discount');
		}
		$address->addTotal(array(
				'code'  => $this->getCode(),
				'title' => $title,
				'value' => $amount
		));
	}
	return $this;
}


}