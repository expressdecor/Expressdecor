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
//$express_email=Mage::app()->getRequest()->getParam('email'); 
$validate_subscription=Mage::app()->getRequest()->getParam('validate_subscription'); //validate subscription from popup and register customer
$facebook_login=Mage::app()->getRequest()->getParam('facebook_login');//if login thru facebook


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


 
 
?>
 