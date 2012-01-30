<?php
/**
 *  Adding flag for 1) Non US orders 2) For different dilling and shipping addresses
 */
class Expressdecor_Salesflag_Model_Expressdecor extends Mage_Core_Model_Abstract
{
	public function ApplyDiscount() {
		$coupon_code=Mage::getModel('checkout/session')->getDiscountParam($param_discount);
		
		if ($coupon_code)
		Mage::getSingleton('checkout/cart')->getQuote()->setCouponCode($coupon_code)->save();		
	}

	public function CheckPromo() {
		$orderId =Mage::getModel('checkout/session')->getLastOrderId();
		$this->edOrder = Mage::getModel('sales/order');
		$id =Mage::getStoreConfig('expressdecor/ses_cook/promo_cookie_param',Mage::app()->getStore());
		
		$promo_code=Mage::getModel('checkout/session')->getPromoCode();
		if (!empty($promo_code)){
			$this->edOrder->load($orderId);
			$this->edOrder->setData('promo_code',$promo_code);
			$this->edOrder->save();
			Mage::getModel('core/cookie')->delete($id, $promo_value);
		}

	}

	public function flagOrder()
	{

		$orderId =Mage::getModel('checkout/session')->getLastOrderId();
		$this->edOrder = Mage::getModel('sales/order');
		$this->edOrder->load($orderId);

		$flag=array();

		$street_bil=$this->edOrder->getBillingAddress()->getStreet();
		$street_ship=$this->edOrder->getShippingAddress()->getStreet();
		$diff_adress=array_diff($street_bil, $street_ship);
		$city=0;
		$postcode=0;
		$region=0;
		$country=0;
		if ($this->edOrder->getBillingAddress()->getCity()!==$this->edOrder->getShippingAddress()->getCity()) {
			$city=1;
		}
		if ($this->edOrder->getBillingAddress()->getPostcode()!==$this->edOrder->getShippingAddress()->getPostcode()) {
			$postcode=1;
		}
		if ($this->edOrder->getBillingAddress()->getRegion()!==$this->edOrder->getShippingAddress()->getRegion()) {
			$region=1;
		}
		if ($this->edOrder->getBillingAddress()->getCountry_id()!==$this->edOrder->getShippingAddress()->getCountry_id()) {
			$country=1;
		}


		if ( ($this->edOrder->getBillingAddress()->getCountry_id()!=='US') or ($this->edOrder->getShippingAddress()->getCountry_id()!=='US') ) {
			$region=0;
		}


		if ( ($city==1)	or ($postcode==1) or ($region==1) or ($country==1) or (count($diff_adress)>0)) {
			array_push($flag,'cc Add');
		}



		if ( ($this->edOrder->getBillingAddress()->getCountry_id()!=='US') or ($this->edOrder->getShippingAddress()->getCountry_id()!=='US') ) {

			array_push($flag,'Non US Add');
		}


		$this->edOrder->setData('sales_flag',implode('/',$flag));
		$this->edOrder->save();


	}



}
