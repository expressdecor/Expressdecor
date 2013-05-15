<?php 

class Expressdecor_Forms_GiveawayController extends Mage_Core_Controller_Front_Action {
	
	
	public function registerAction() {
		if ($this->getRequest ()->isXmlHttpRequest ()) {
			 
			$data=Mage::app()->getRequest()->getParams();
			$data['date_created']=Mage::getModel('core/date')->timestamp(time());
			$giveaway=Mage::getModel('forms/giveaway');						
			$giveaway->setData($data)->save();						
			$messages="ok";
			 
			$this->getResponse ()->setBody ($messages);
		}else {
			$this->_redirect('');
		}
	}
} 