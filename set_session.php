<?php
/** 
 * customer_show - left Need help Window
 * express_mail_popup_window - popup window for email subscription 
 */

require_once 'app/Mage.php';
//Mage::getSingleton("customer/session", array("name" => "frontend"));

$is_online=Mage::app()->getRequest()->getParam('customer_servive_is_online'); // for left Need help Panel
$expressmail_cookie_value=Mage::app()->getRequest()->getParam('show_express_email_set_cookie_value'); //For Quote
$expressmail_show=Mage::app()->getRequest()->getParam('express_mail_show'); // If pop-up Closed 
$express_email=Mage::app()->getRequest()->getParam('email'); 
$validate_subscription=Mage::app()->getRequest()->getParam('validate_subscription'); //validate subscription from popup and register customer

/**
 *  Parameter for left Need Help panel
 */
if (isset($is_online) && $is_online>=0) { //deprecated
	//Prepare time virables
	$hours=array();
	$hours=Mage::getStoreConfig('expressdecor/customer_service/service_hours',Mage::app()->getStore());
	$hours=json_decode($hours);
	$day=date('D', Mage::getModel('core/date')->timestamp(time()));
	$last_day=date('D',strtotime('-1 day',Mage::getModel('core/date')->timestamp(time())));
	
	if (strpos($hours->customer_service_time->$last_day->end,'+')>0) {
	
		$end_time_config_last=$hours->customer_service_time->$last_day->end;
		$end_time_config_last=str_replace('+','',$end_time_config_last);
	
		$h_l=substr($end_time_config_last,0,strpos($end_time_config_last,':'));
		$m_l=substr($end_time_config_last,strpos($end_time_config_last,':')+1,strlen($end_time_config_last)-strpos($end_time_config_last,':')-1);
	
		if (strtotime(date("M-d-Y H:i:s", Mage::getModel('core/date')->timestamp(time())))<mktime($h_l,$m_l,date("s", Mage::getModel('core/date')->timestamp(time())),date("m", Mage::getModel('core/date')->timestamp(time())),date("d", Mage::getModel('core/date')->timestamp(time())),date("Y", Mage::getModel('core/date')->timestamp(time())))) {
			$day=$last_day;
		}
	}	
	
	$end_time_config=$hours->customer_service_time->$day->end;
	$start_date=strtotime($hours->customer_service_time->$day->start);
	
	if(strpos($end_time_config,"+")>0)
	{
	
		$end_time_config=str_replace('+','',$end_time_config);
		$h=substr($end_time_config,0,strpos($end_time_config,':'));
		$m=substr($end_time_config,strpos($end_time_config,':')+1,strlen($end_time_config)-strpos($end_time_config,':')-1);
		$end_time=mktime($h,$m,date("s", Mage::getModel('core/date')->timestamp(time())),date("m", Mage::getModel('core/date')->timestamp(time())),date("d", Mage::getModel('core/date')->timestamp(time()))+1,date("Y", Mage::getModel('core/date')->timestamp(time())));
		$start_date='0:00';
	
	} else {
		$h=substr($end_time_config,0,strpos($end_time_config,':'));
		$m=substr($end_time_config,strpos($end_time_config,':')+1,strlen($end_time_config)-strpos($end_time_config,':')-1);
		$end_time=mktime($h,$m,date("s", Mage::getModel('core/date')->timestamp(time())),date("m", Mage::getModel('core/date')->timestamp(time())),date("d", Mage::getModel('core/date')->timestamp(time())),date("Y", Mage::getModel('core/date')->timestamp(time())));
	
	}
 	// Check if customer service is online
	if ( ($start_date < strtotime(date('H:i', Mage::getModel('core/date')->timestamp(time()))) ) &&
		 ( strtotime(date("M-d-Y H:i:s", Mage::getModel('core/date')->timestamp(time()))) < $end_time )  ) {
		$is_open=1;
	} else {
		$is_open=0;
	}
	echo Mage::helper('core')->jsonEncode(array('customer_service_online'=>$is_open,
												'time_from'=>date('g A',strtotime($hours->customer_service_time->$day->start)),
												'time_to'=>date('g A',$end_time)));
}


/**
 * Ajax checking if popup windows wasn't already closed
 */
if (isset($expressmail_show) && $expressmail_show>=0) {
	$mail=Mage::getModel('core/cookie')->get('express_mail_popup_window');
	if ($mail>0){
	    echo Mage::helper('core')->jsonEncode(array('show'=>'0'));
	} else {		
		echo Mage::helper('core')->jsonEncode(array('show'=>'1'));
	}
}
/**
 *  Validate email and subscribe user
 */
if (isset($validate_subscription) && $validate_subscription>=0) {
	Mage::app ( 'default' );

	$ownerId = Mage::getModel('customer/customer')
	->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
	->loadByEmail($express_email)
	->getId();
	
	if ($ownerId !== null) {
		echo Mage::helper('core')->jsonEncode(array('result'=>'This email address is already assigned to another user.'));	
		 die();
	}
		
	$status = Mage::getModel('newsletter/subscriber')->subscribe($express_email);
	echo Mage::helper('core')->jsonEncode(array('result'=>'success'));
 
	// create customer if we need that
	// 		/* $customer_email = 'aliaksandrl@expressdecor.com';  // email adress that will pass by the questionaire
	// 		$customer_fname = 'Customer';      // we can set a tempory firstname here
	// 		$customer_lname = '';       // we can set a tempory lastname here
	// 		$passwordLength = 10;                    // the lenght of autogenerated password
	
	// 		$customer = Mage::getModel('customer/customer');
	// 		$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
	// 		$customer->loadByEmail($customer_email);
	
	// 		/*
	// 		 * Check if the email exist on the system.
	// 		* If YES,  it will not create a user account.
	// 		*/
	
	// 		if(!$customer->getId()) {
	// 			//setting data such as email, firstname, lastname, and password
	// 			$customer->setEmail($customer_email);
	// 			$customer->setFirstname($customer_fname);
	// 			$customer->setLastname($customer_lname);
	// // 			$customer->setPassword($customer->generatePassword($passwordLength));
	// 		}
	// 		try{
	// 			//the save the data and send the new account email.
	// 			$customer->setPassword($customer->generatePassword($passwordLength));
	// 			$customer->save();
	// 			$customer->setConfirmation(null);
	// 			$customer->save();
	// 			$customer->sendNewAccountEmail();
	// 		}
	// 		catch(Exception $ex){
	
	// 		 */}
	//end of create customer
}

/**
 * Save cookie value if popup Window closed or customer was subscribed
 */
if (isset($expressmail_cookie_value) && $expressmail_cookie_value>=0) {
	Mage::app ( 'default' );
	
	$time =60*60*24*7*4*$expressmail_cookie_value; // 4 Weeks
	Mage::getModel('core/cookie')->set('express_mail_popup_window', $expressmail_cookie_value, $time);
	 
	if (isset($express_email) && strlen($express_email)>0) {				
		Mage::getModel('core/cookie')->set('express_email', $express_email, $time);		 							
		/*
		 * If Informaion Correct => set Email for the Quote 
	    */
		Mage::run('','store');
		$session = Mage::getSingleton('checkout/session');	
		$core_session = Mage::getSingleton('core/session');
		$core_session->addSuccess('Thank you for your subscription.');
		
		$session_quote_id=$session->getQuote()->getId();
		if (!empty($session_quote_id)) {
			$session->getQuote()->setCustomerEmail($express_email)->save();
		}			 
	}
	 
}   

 
?>
 