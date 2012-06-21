<?php 


class Expressdecor_Ordercomments_Model_Sales_Order extends Mage_Sales_Model_Order

{
	/**
	 * Send email with order update information
	 *
	 * @param boolean $notifyCustomer
	 * @param string $comment
	 * @return Mage_Sales_Model_Order
	 */
	public function sendOrderUpdateEmail($notifyCustomer = true, $comment = '')
	{
		$storeId = $this->getStore()->getId();
		$comment=nl2br($comment); // Alex
		if (!Mage::helper('sales')->canSendOrderCommentEmail($storeId)) {
			return $this;
		}
		// Get the destination email addresses to send copies to
		$copyTo = $this->_getEmails(self::XML_PATH_UPDATE_EMAIL_COPY_TO);
		$copyMethod = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_COPY_METHOD, $storeId);
		// Check if at least one recepient is found
		if (!$notifyCustomer && !$copyTo) {
			return $this;
		}
	
		// Retrieve corresponding email template id and customer name
		if ($this->getCustomerIsGuest()) {
			$templateId = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_GUEST_TEMPLATE, $storeId);
			$customerName = $this->getBillingAddress()->getName();
		} else {
			$templateId = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_TEMPLATE, $storeId);
			$customerName = $this->getCustomerName();
		}
	
		$mailer = Mage::getModel('core/email_template_mailer');
		if ($notifyCustomer) {
			$emailInfo = Mage::getModel('core/email_info');
			$emailInfo->addTo($this->getCustomerEmail(), $customerName);
			if ($copyTo && $copyMethod == 'bcc') {
				// Add bcc to customer email
				foreach ($copyTo as $email) {
					$emailInfo->addBcc($email);
				}
			}
			$mailer->addEmailInfo($emailInfo);
		}
	
		// Email copies are sent as separated emails if their copy method is
		// 'copy' or a customer should not be notified
		if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
			foreach ($copyTo as $email) {
				$emailInfo = Mage::getModel('core/email_info');
				$emailInfo->addTo($email);
				$mailer->addEmailInfo($emailInfo);
			}
		}
	
		// Set all required params and send emails
		$mailer->setSender(Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $storeId));
		$mailer->setStoreId($storeId);
		$mailer->setTemplateId($templateId);
		$mailer->setTemplateParams(array(
				'order'   => $this,
				'comment' => $comment,
				'billing' => $this->getBillingAddress()
		)
		);
		$mailer->send();
	
		return $this;
	}
	
	public function addStatusHistoryComment($comment, $status = false)
	{
		$comment=nl2br($comment); //<br/>
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