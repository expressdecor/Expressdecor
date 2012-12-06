<?php

class Expressdecor_Facebook_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function getAppId() {
		return Mage::getStoreConfig('facebook/settings/appid');
	}
	
	public function getSecretKey() {
		return Mage::getStoreConfig('facebook/settings/secret');
	}
		 
}
