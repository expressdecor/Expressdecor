<?php
/**
 * Magento Order Editor Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the License Version.
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 *
 * @category   Order Editor
 * @package    Oeditor_Ordereditor
 * @copyright  Copyright (c) 2010 
 * @version    0.4.1
*/

class Oeditor_Ordereditor_Adminhtml_OrdereditorController extends Mage_Adminhtml_Controller_action
{
	private $_order;


	public function saveAction() {
		$field = $this->getRequest()->getParam('field');
		$type = $this->getRequest()->getParam('type');
		$orderId = $this->getRequest()->getParam('order');
		$value = $this->getRequest()->getPost('value');
		if (!empty($field) && !empty($type) && !empty($orderId)) {
			if(!empty($value)) {
				if(!$this->_loadOrder($orderId)) {
					$this->getResponse()->setBody($this->__('error: missing order number'));
				}
				$res = $this->_editAddress($type,$field,$value);
				if($res !== true) {
					$this->getResponse()->setBody($this->__('error: '.$res));
				} else {

					if($field == "order_status"){
						$statuses = Mage::getSingleton('sales/order_config')->getStatuses();
						foreach($statuses as $key=>$keyValue)
						{
							if($key == $value) { $this->getResponse()->setBody($keyValue);break;}
						}

					}

					else{$this->getResponse()->setBody($value); }
				}
			} else {
				$this->getResponse()->setBody($this->__('error: value required'));
			}
		} else {
			$this->getResponse()->setBody('undefined error');
		}
	}

	private function _loadOrder($orderId) {
		$this->_order = Mage::getModel('sales/order')->load($orderId);
		if(!$this->_order->getId()) return false;
		return true;
	}

	private function _editAddress($type,$field,$value) {
		//echo $type.'='.$field.'='.$value;die;
		if($type == "bill") {
			$address = $this->_order->getBillingAddress();

			$addressSet = 'setBillingAddress';
		} elseif($type == "ship") {
			$address = $this->_order->getShippingAddress();
			$addressSet = 'setShippingAddress';
		} elseif($type == "cemail") {
			$this->_order->setCustomerEmail($value)->save();
			return true;
		} elseif($type == "sales_flag") { //modified by alex : adding sales_flag
			if ($value=="No flag") $value='';
			//			$this->_order->setSalesFlag($value)->save();
			$this->_order->setData('sales_flag',$value)->save();
			return true;
		}  elseif($type == "channel") { //modified by alex : adding channel

			$this->_order->setData('channel',$value)->save();
			return true;
		} elseif($type == "cust_name") {

			$explodeName = explode(" ",$value);
			if(isset($explodeName[0]) && $explodeName[0] != ""){ $firstName = $explodeName[0]; $this->_order->setCustomerFirstname($firstName)->save();}
			if(isset($explodeName[1]) && $explodeName[1] != ""){ $lastName = $explodeName[1]; $this->_order->setCustomerLastname($lastName)->save();}


			return true;
		} elseif($type == "edit_ord") {
			$this->_order->setStatus($value)->save();
			return true;
		}

		else {
			return 'type not defined';
		}

		$updated = false;
		$fieldGet = 'get'.ucwords($field);
		$fieldSet = 'set'.ucwords($field);


		if($address->$fieldGet() != $value) {

			if($field == 'country') {
				$fieldSet = 'setCountryId';
				$countries = array_flip(Mage::app()->getLocale()->getCountryTranslationList());
				if(isset($countries[$value])) {
					$value = $countries[$value];
				} else {
					return 'country not found';
				}
			}
			if(substr($field,0,6) == 'street') {
				$i = substr($field,6,1);
				if(!is_numeric($i))
				$i = 1;
				$valueOrg = $value;
				$value = array();
				for($n=1;$n<=4;$n++) {
					if($n != $i) {
						$value[] = $address->getStreet($n);
					} else {
						$value[] = $valueOrg;
					}
				}
				$fieldSet = 'setStreet';
			}
			//update field and set as updated
			$address->$fieldSet($value);
			$updated = true;
		}

		if($updated) {
			//			$this->_order->setStatus($value)->save();
			if($field == "firstname") {
				$this->_order->setFirstName($value)->save();
				return true;
			}
			if($field == "lastname") {
				$this->_order->setLastName($value)->save();
				return true;
			}

			if($field == "street1") {
				$this->_order->setStreet1($value)->save();
				return true;
			}

			if($field == "street2") {
				$this->_order->setStreet2($value)->save();
				return true;
			}

			if($field == "street3") {
				$this->_order->setStreet3($value)->save();
				return true;
			}
			if($field == "street4") {
				$this->_order->setStreet4($value)->save();
				return true;
			}

			if($field == "city") {
				$this->_order->setCity($value)->save();
				return true;
			}
			if($field == "region") {
				$this->_order->setRegion($value)->save();
				return true;
			}
			if($field == "postcode") {
				$this->_order->setPostcode($value)->save();
				return true;
			}
			if($field == "country") {
				$this->_order->setCountry($value)->save();
				return true;
			}
			if($field == "telephone") {
				$this->_order->setTelephone($value)->save();
				return true;
			}
			if($field == "fax") {
				$this->_order->setFax($value)->save();
				return true;
			}
			if($field == "sales_flag") {  //addes by Alex : adding sales_flag
				if ($value=="No flag") $value='';
				$this->_order->setData('sales_flag',$value)->save();
				return true;
			}
			if($field == "channel") {  //addes by Alex : adding channel
				$this->_order->setData('channel',$value)->save();
				return true;
			}

			$this->_order->$addressSet($address);
			$this->_order->save();
		}
		return true;
	}
}