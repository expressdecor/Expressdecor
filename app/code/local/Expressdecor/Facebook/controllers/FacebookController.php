<?php
 
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@expressdecor.com so we can send you a copy immediately.
 *
 * @author Alex Lukyanov
 * @copyright   Copyright (c) 2013 ExpressDecor. (http://www.expressdecor.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Created: Mar 7, 2013
 *
 */
class Expressdecor_Facebook_FacebookController extends Mage_Core_Controller_Front_Action {
	
	/**
	 * Function return parameter to show window
	 */
	public function isloggedAction () {
		if ($this->getRequest ()->isXmlHttpRequest ()) {
			  
			$show_register_invitation = Mage::getSingleton('core/session')->getShowregiwindow();
			$visitor_data=Mage::getSingleton('core/session')->getData('visitor_data');
			$visitor_logout=(   strpos($visitor_data['http_referer'],'logoutSuccess') ? Mage::getSingleton('core/session')->setShowregiwindow(1) : 0 );
			 
			if (Mage::helper('customer')->isLoggedIn() || $show_register_invitation || $visitor_logout) {
				$messages = 1;
			}else {
				$messages=0;
			}
			$this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $messages ) );
		}
	}
	
	public function closewindowAction () {
		if ($this->getRequest ()->isXmlHttpRequest ()) {				
			$show_register_invitation = Mage::getSingleton('core/session')->setShowregiwindow(1);
			$messages="success";
			$this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $messages ) );
		}
	}
	
	/**
	 *  Login to the site from Pop-up window
	 */
	public function loginAction () {
		if ($this->getRequest ()->isXmlHttpRequest ()) {
			
			$express_email=Mage::app()->getRequest()->getParam('email');
			$express_password=Mage::app()->getRequest()->getParam('password');
			$type=Mage::app()->getRequest()->getParam('type');
			$passwordLength = 10;
			
			try {
			$customer = Mage::getModel('customer/customer')
			->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
			->loadByEmail($express_email);			 
			
			/* Check if we already have user with this email*/ 			
			if(!$customer->getId()) {
				if ($type==2)
					throw new Exception($this->__("Accpunt with this email doesn't exist."));
				$customer->setEmail($express_email);	
				if (!$express_passwrod)
					$express_password=$customer->generatePassword($passwordLength);
				$customer->setIsSubscribed(1); 
	 			$customer->setPassword($express_password);
	 			//the save the data and send the new account email.	 			 
	 			$customer->save();
	 			$customer->setConfirmation(null);
	 			$customer->save();
	 			$customer->sendNewAccountEmail();
	 			 
			} else {
				if ($type==1 || $type==3 )
					throw new Exception($this->__("This email address already associated with account. Please Sign In."));								
			}
			
			$login = Mage::getSingleton ( 'customer/session' )->login($express_email,$express_password);			
 
			} catch(Exception $e) {
				$messages = $e->getMessage();
				if ($messages=="Invalid login or password.") {				 
					$messages=$this->__("Invalid password for this email address.");				 					
				}
			}
			 			
			if (Mage::helper('customer')->isLoggedIn()) {				
				$messages = "success";
			}  			
			
			$this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $messages ) );
		}
	}
	
	public function forgotpassAction(){
		if ($this->getRequest ()->isXmlHttpRequest ()) {
			$express_email=Mage::app()->getRequest()->getParam('email');
		
			$customer = Mage::getModel('customer/customer')
			->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
			->loadByEmail($express_email);
			try {    
	       		if ($customer->getEmail()) {
				 	$newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();			 	
					$customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
					$customer->sendPasswordResetConfirmationEmail();		
					 
					 
	       		} else {
	       			throw new Exception($this->__('Account with this email doesn\'t exist.'));	       			
	       		}
			} catch(Exception $e) {
				$messages = $e->getMessage();
			}	       
			if (empty($messages)){
				$messages = $this->__('You will receive an email with a link to reset your password.');
			}
	       $this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $messages) );
		}
	}
	
	/**
	 * 
	 */
	public function updatelinksAction(){
		if ($this->getRequest ()->isXmlHttpRequest ()) {			 
			$links_block=$this->getLayout()->createBlock('page/template_links');
			$links_block->addLink('My Account','/customer/account/','My Account','','',1);
			$links_block->addLink('Log Out ','/customer/account/logout/','Log Out','','',2);			 
			$this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $links_block->toHtml() ) );
		}
	}
	/**
	 * FACEBOOK FUNCTIONS
	 */
	public function fbloginAction() {
		if ($this->getRequest ()->isXmlHttpRequest ()) {
			$me = null;
			$cookie = $this->get_facebook_cookie ( Mage::getStoreConfig ( 'facebook/settings/appid' ), Mage::getStoreConfig ( 'facebook/settings/secret' ) );
			
			$me = json_decode ( $this->getFbData ( 'https://graph.facebook.com/me?access_token=' . $cookie ['access_token'] ) );
			 
			if (! is_null ( $me )) {
				$me = ( array ) $me;
				
				$session = Mage::getSingleton ( 'customer/session' );
				$website_id = Mage::getModel ( 'core/store' )->load ( Mage::app ()->getStore ()->getStoreId () )->getWebsiteId ();
				$store_id = Mage::app ()->getStore ()->getStoreId ();
				
				$db = Mage::getSingleton ( 'core/resource' )->getConnection ( 'facebook_write' );
				$tablePrefix = ( string ) Mage::getConfig ()->getTablePrefix ();
				
				$select = $db->select ()->from ( $tablePrefix . 'expressdecor_facebook_customer' )->where ( 'fb_id = ' . $me ['id'] . ' AND `website_id` = "' . $website_id . '" AND `store_id` = "' . $store_id . '" ' )->limit ( 1 );
				$data = $db->fetchRow ( $select );
				
				if ($data) {
					$session->loginById ( $data ['customer_id'] );
				
				} else {
					$select = $db->select ()->from ( $tablePrefix . 'customer_entity' )->where ( 'email = "' . $me ['email'] . '" AND `website_id` = "' . $website_id . '" AND `store_id` = "' . $store_id . '" ' )->limit ( 1 );
					$r = $db->fetchRow ( $select );
					
					if ($r) {
						$db_write = Mage::getSingleton ( 'core/resource' )->getConnection ( 'facebook_write' );
						$db_write->beginTransaction ();
						$data = array ('customer_id' => $r ['entity_id'], 'store_id' => $store_id, 'website_id' => $website_id, 'fb_id' => $me ['id'] );
						$db_write->insert ( $tablePrefix . 'expressdecor_facebook_customer', $data );
						$db_write->commit ();
						/*
						 * Alex changes
						 */
						// $model =
						// Mage::getModel('facebook/facebook')->setData($data);
						// $model->save();
						
						$session->loginById ( $r ['entity_id'] );
					} else {
						$customer = Mage::getModel ( 'customer/customer' )->setId ( null );
						$customer->setData ( 'firstname', $me ['first_name'] );
						$customer->setData ( 'lastname', $me ['last_name'] );
						$customer->setData ( 'email', $me ['email'] );
						$customer->setData ( 'password', substr(md5 ( time () . $me ['id'] . $me ['locale'] ), 0,6) );
						$customer->setData ( 'is_active', 1 );
						$customer->setData ( 'confirmation', null );
						
						$customer->getGroupId ();
						if ( $me ['gender']=='male'){
							$customer->setData ( 'gender', 1 );
						}
						if ( $me ['gender']=='female'){
							$customer->setData ( 'gender', 2 );
						}						
						$customer->save ();
						
						Mage::getModel ( 'customer/customer' )->load ( $customer->getId () )->setConfirmation ( null )->save ();
						$customer->sendNewAccountEmail();
						$session->setCustomerAsLoggedIn ( $customer );
						$customer_id = $session->getCustomerId ();
						
						/*
						 * Added 2 rows by alex
						 */
						$db_write = Mage::getSingleton ( 'core/resource' )->getConnection ( 'facebook_write' );
						$db_write->beginTransaction ();
						
						$data = array ('customer_id' => $customer_id, 'store_id' => $store_id, 'website_id' => $website_id, 'fb_id' => $me ['id'] );
						// $model =
						// Mage::getModel('facebook/facebook')->setData($data);
						// $model->save();
						/*
						 * Alex changes
						 */
						
						$db_write->insert ( $tablePrefix . 'expressdecor_facebook_customer', $data );
						$db_write->commit ();
					}
				}
				if (Mage::helper('customer')->isLoggedIn()) {					
					$messages = "success";					
				} else {
					$messages = "error";
				}
				$this->getResponse ()->setBody ( Mage::helper ( 'core' )->jsonEncode ( $messages ) );
			}
		}
	}
	
	/**
	 * FACEBOOK FUNCTIONS
	 */
	private function get_facebook_cookie($app_id, $app_secret) {
		if ($_COOKIE ['fbsr_' . $app_id] != '') {
			return $this->get_new_facebook_cookie ( $app_id, $app_secret );
		} else {
			return $this->get_old_facebook_cookie ( $app_id, $app_secret );
		}
	}
	
	private function get_old_facebook_cookie($app_id, $app_secret) {
		$args = array ();
		parse_str ( trim ( $_COOKIE ['fbs_' . $app_id], '\\"' ), $args );
		ksort ( $args );
		$payload = '';
		foreach ( $args as $key => $value ) {
			if ($key != 'sig') {
				$payload .= $key . '=' . $value;
			}
		}
		if (md5 ( $payload . $app_secret ) != $args ['sig']) {
			return array ();
		}
		return $args;
	}
	
	private function get_new_facebook_cookie($app_id, $app_secret) {
		$signed_request = $this->parse_signed_request ( $_COOKIE ['fbsr_' . $app_id], $app_secret );
		// $signed_request should now have most of the old elements
		$signed_request ['uid'] = $signed_request ['user_id']; // for compatibility
		if (! is_null ( $signed_request )) {
			// the cookie is valid/signed correctly
			// lets change "code" into an "access_token"
			$access_token_response = $this->getFbData ( "https://graph.facebook.com/oauth/access_token?client_id=$app_id&redirect_uri=&client_secret=$app_secret&code=$signed_request[code]" );
			parse_str ( $access_token_response );
			$signed_request ['access_token'] = $access_token;
			$signed_request ['expires'] = time () + $expires;
		}
		return $signed_request;
	}
	
	private function parse_signed_request($signed_request, $secret) {
		list ( $encoded_sig, $payload ) = explode ( '.', $signed_request, 2 );
		
		// decode the data
		$sig = $this->base64_url_decode ( $encoded_sig );
		$data = json_decode ( $this->base64_url_decode ( $payload ), true );
		
		if (strtoupper ( $data ['algorithm'] ) !== 'HMAC-SHA256') {
			error_log ( 'Unknown algorithm. Expected HMAC-SHA256' );
			return null;
		}
		
		// check sig
		$expected_sig = hash_hmac ( 'sha256', $payload, $secret, $raw = true );
		if ($sig !== $expected_sig) {
			error_log ( 'Bad Signed JSON signature!' );
			return null;
		}
		
		return $data;
	}
	
	function base64_url_decode($input) {
		return base64_decode ( strtr ( $input, '-_', '+/' ) );
	}
	
	function getFbData($url) {
		$data = null;
		
		if (ini_get ( 'allow_url_fopen' ) && function_exists ( 'file_get_contents' )) {
			$data = file_get_contents ( $url );
		} else {
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			$data = curl_exec ( $ch );
		}
		return $data;
	}
	
	/*
	 * FACEBOOK FUNCTIONS
	 */

}
