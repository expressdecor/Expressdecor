<?php
require_once 'app/Mage.php';
Mage::app('default');

$post = Mage::app()->getRequest()->getPost();

if ( $post ) {
	$postObject = new Varien_Object();
	$postObject->setData($post);

	$mailTemplate = Mage::getModel('core/email_template');
	/* @var $mailTemplate Mage_Core_Model_Email_Template */
	$mailTemplate->setDesignConfig(array('area' => 'frontend'))
	->setReplyTo($post['email'])
	->sendTransactional(
	Mage::getStoreConfig('contacts/email/email_template'),
	Mage::getStoreConfig('contacts/email/sender_email_identity'),
	Mage::getStoreConfig('contacts/email/recipient_email'),
	null,
	array('data' => $postObject)
	);

	if (!$mailTemplate->getSentSuccess()) {
		echo "Fail";
	} else
	{
		echo "Good";
	}
} else {
	echo "No data";
}






?>
