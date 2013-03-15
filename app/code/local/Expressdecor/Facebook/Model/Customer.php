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
 * Created: Mar 15, 2013
 *
 */
class Expressdecor_Facebook_Model_Customer extends Mage_Customer_Model_Customer {
	/**
	 * Validate customer attribute values.
	 * For existing customer password + confirmation will be validated only when password is set (i.e. its change is requested)
	 *
	 * @return bool
	 */
	public function validate() {
		$errors = array (); 
		
		if (! Zend_Validate::is ( $this->getEmail (), 'EmailAddress' )) {
			$errors [] = Mage::helper ( 'customer' )->__ ( 'Invalid email address "%s".', $this->getEmail () );
		}
		
		$password = $this->getPassword ();
		if (! $this->getId () && ! Zend_Validate::is ( $password, 'NotEmpty' )) {
			$errors [] = Mage::helper ( 'customer' )->__ ( 'The password cannot be empty.' );
		}
		if (strlen ( $password ) && ! Zend_Validate::is ( $password, 'StringLength', array (
				6 
		) )) {
			$errors [] = Mage::helper ( 'customer' )->__ ( 'The minimum password length is %s', 6 );
		}
		$confirmation = $this->getConfirmation ();
		if ($password != $confirmation) {
			$errors [] = Mage::helper ( 'customer' )->__ ( 'Please make sure your passwords match.' );
		}
		
		$entityType = Mage::getSingleton ( 'eav/config' )->getEntityType ( 'customer' );
		$attribute = Mage::getModel ( 'customer/attribute' )->loadByCode ( $entityType, 'dob' );
		if ($attribute->getIsRequired () && '' == trim ( $this->getDob () )) {
			$errors [] = Mage::helper ( 'customer' )->__ ( 'The Date of Birth is required.' );
		}
		$attribute = Mage::getModel ( 'customer/attribute' )->loadByCode ( $entityType, 'taxvat' );
		if ($attribute->getIsRequired () && '' == trim ( $this->getTaxvat () )) {
			$errors [] = Mage::helper ( 'customer' )->__ ( 'The TAX/VAT number is required.' );
		}
		$attribute = Mage::getModel ( 'customer/attribute' )->loadByCode ( $entityType, 'gender' );
		if ($attribute->getIsRequired () && '' == trim ( $this->getGender () )) {
			$errors [] = Mage::helper ( 'customer' )->__ ( 'Gender is required.' );
		}
		
		if (empty ( $errors )) {
			return true;
		}
		return $errors;
	}
}