<?php
require_once 'app/Mage.php';
Mage::app('default');

$post = Mage::app()->getRequest()->getPost();

if ( $post ) {
	$postObject = new Varien_Object();
	$postObject->setData($post);
	$email_template=Mage::getStoreConfig('contacts/email/email_template');
	$sender_email_identity=Mage::getStoreConfig('contacts/email/sender_email_identity');
	$recipient_email=Mage::getStoreConfig('contacts/email/recipient_email');
	$from=array('name'=>'Feedback', 'email'=>$post['email']);
 
	$mailTemplate = Mage::getModel('core/email_template');
 
	/* @var $mailTemplate Mage_Core_Model_Email_Template */
	$mailTemplate->setDesignConfig(array('area' => 'frontend'))
	->setReplyTo($post['email'])
	->sendTransactional(
	$email_template,
	$from,
	$recipient_email,
	null,
	array('data' => $postObject)
	);

	if (!$mailTemplate->getSentSuccess()) {
		echo "Fail";
	} else
	{
		echo "Good";
		//Sent Email to Admins		
		$mailTemplate->setDesignConfig(array('area' => 'frontend'))
		->setReplyTo($post['email']) 
		->sendTransactional(
				$email_template,
				 $from,
				'alukyanov@expressdecor.com',
				null,
				array('data' => $postObject)
		);
		$mailTemplate->setDesignConfig(array('area' => 'frontend'))
		->setReplyTo($post['email'])
		->sendTransactional(
				$email_template,
				$from,
				'kbedi@expressdecor.com',
				null,
				array('data' => $postObject)
		);
	}
} else {
	echo "No data";
}






?>
