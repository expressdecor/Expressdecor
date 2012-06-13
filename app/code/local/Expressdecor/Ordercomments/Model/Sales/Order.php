<?php 


class Expressdecor_Ordercomments_Model_Sales_Order extends Mage_Sales_Model_Order

{
	
	public function addStatusHistoryComment($comment, $status = false)
	{
// 		if (false === $status) {
			$status = $this->getStatus();
	/*	} elseif (true === $status) {
			$status = $this->getConfig()->getStateDefaultStatus($this->getState());
		} else {
			$this->setStatus($status);
		}*/
		$UserInfo = Mage::getSingleton('admin/session')->getUser();
		$UserName='';
		if (!empty($UserInfo)) { // add for fix error if order placed from front end					
		$UserName=$UserInfo->getUsername();
		}
		$history = Mage::getModel('sales/order_status_history')
		->setStatus($status)
		->setComment($comment)
		->setTrackUser($UserName); //added by alex to add users for comments
		parent::addStatusHistory($history);
		
		return $history;

	}
	

}