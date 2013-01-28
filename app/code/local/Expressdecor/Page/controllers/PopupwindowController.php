<?php
class Expressdecor_Page_PopupwindowController extends Mage_Core_Controller_Front_Action {

	public function GetparametersAction () {
		if ($this->getRequest ()->isXmlHttpRequest ()) {
			
			$coupon=Mage::app()->getRequest()->getParam('coupon');
			

			$coupon_model=Mage::getModel('salesrule/coupon')->load($coupon, 'code');
			$coupon_code=$coupon_model->getCode();			
			$coupon_labels=Mage::getModel('salesrule/rule')->load($coupon_model->getRuleId())->getStoreLabels();
			
			$coupon_model=Mage::getModel('salesrule/coupon')->load($coupon, 'code');
			$coupon_code=$coupon_model->getCode();
			
			$coupon_labels=Mage::getModel('salesrule/rule')->load($coupon_model->getRuleId())->getStoreLabels();
			
			if ( empty ($coupon_labels[Mage::app()->getStore()->getId()])) {
				$label=	$coupon_labels[0];
			} else {
				$label=	$coupon_labels[Mage::app()->getStore()->getId()];
			}												 
			$messages = array('label'=>$label,'code'=>$coupon_code);
			$this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $messages ) );
		}
	}
}