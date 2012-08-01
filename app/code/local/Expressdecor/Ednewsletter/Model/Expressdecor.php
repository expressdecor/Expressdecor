<?php

class Expressdecor_Ednewsletter_Model_Expressdecor extends Mage_Core_Model_Abstract
{
 	public function Setupsource(Varien_Event_Observer $observer){
 		$subscriber = $observer->getEvent()->getSubscriber();
 		$source=Mage::app()->getRequest()->getParam('source');
 		$old_source=$subscriber->getSource();
 		if (empty($old_source)){
 			$subscriber->setSource($source);
 		}  
 		return $this; 	
 	}
}
  