<?php
class Expressdecor_Shipping_Model_Carrier_Ups
extends Mage_Usa_Model_Shipping_Carrier_Ups
//implements Mage_Shipping_Model_Carrier_Interface
{
/**
 * Prepare shipping rate result based on response
 *
 * @param mixed $response
 * @return Mage_Shipping_Model_Rate_Result
 */
protected function _parseXmlResponse($xmlResponse)
{
	$costArr = array();
	$priceArr = array();
	if (strlen(trim($xmlResponse))>0) {
		$xml = new Varien_Simplexml_Config();
		$xml->loadString($xmlResponse);
		$arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/ResponseStatusCode/text()");
		$success = (int)$arr[0];
		if ($success===1) {
			$arr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment");
			$allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

			// Negotiated rates
			$negotiatedArr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment/NegotiatedRates");
			$negotiatedActive = $this->getConfigFlag('negotiated_active')
			&& $this->getConfigData('shipper_number')
			&& !empty($negotiatedArr);

			$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();

			foreach ($arr as $shipElement){
				$code = (string)$shipElement->Service->Code;
				if (in_array($code, $allowedMethods)) {

					if ($negotiatedActive) {
						$cost = $shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
					} else {
						$cost = $shipElement->TotalCharges->MonetaryValue;
					}

					//convert price with Origin country currency code to base currency code
					$successConversion = true;
					$responseCurrencyCode = (string) $shipElement->TotalCharges->CurrencyCode;
					if ($responseCurrencyCode) {
						if (in_array($responseCurrencyCode, $allowedCurrencies)) {
							$cost = (float) $cost * $this->_getBaseCurrencyRate($responseCurrencyCode);
						} else {
							$errorTitle = Mage::helper('directory')->__('Can\'t convert rate from "%s-%s".', $responseCurrencyCode, $this->_request->getPackageCurrency()->getCode());
							$error = Mage::getModel('shipping/rate_result_error');
							$error->setCarrier('ups');
							$error->setCarrierTitle($this->getConfigData('title'));
							$error->setErrorMessage($errorTitle);
							$successConversion = false;
						}
					}

					if ($successConversion) {// print_r((string)$shipElement->RatedPackage->Weight); Alex changes for shippind handle
						$costArr[$code] = $cost;
						$priceArr[$code] = $this->getMethodPrice(floatval($cost),$code);  // code =03 standart ground 
						 if ($cost!=$this->getMethodPrice(floatval($cost),$code) && (string)$shipElement->RatedPackage->Weight < 5 && $code==03) {
						 	$priceArr[$code] =$priceArr[$code]/2;
						 }
					}
				}
			}
		} else {
			$arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/Error/ErrorDescription/text()");
			$errorTitle = (string)$arr[0][0];
			$error = Mage::getModel('shipping/rate_result_error');
			$error->setCarrier('ups');
			$error->setCarrierTitle($this->getConfigData('title'));
			$error->setErrorMessage($this->getConfigData('specificerrmsg'));
		}
	}

	$result = Mage::getModel('shipping/rate_result');
	$defaults = $this->getDefaults();
	if (empty($priceArr)) {
		$error = Mage::getModel('shipping/rate_result_error');
		$error->setCarrier('ups');
		$error->setCarrierTitle($this->getConfigData('title'));
		if(!isset($errorTitle)){
			$errorTitle = Mage::helper('usa')->__('Cannot retrieve shipping rates');
		}
		$error->setErrorMessage($this->getConfigData('specificerrmsg'));
		$result->append($error);
	} else {
		foreach ($priceArr as $method=>$price) {
			$rate = Mage::getModel('shipping/rate_result_method');
			$rate->setCarrier('ups');
			$rate->setCarrierTitle($this->getConfigData('title'));
			$rate->setMethod($method);
			$method_arr = $this->getShipmentByCode($method);
			$rate->setMethodTitle($method_arr);
			$rate->setCost($costArr[$method]);
			$rate->setPrice($price);
			$result->append($rate);
		}
	}
	return $result;
}

}