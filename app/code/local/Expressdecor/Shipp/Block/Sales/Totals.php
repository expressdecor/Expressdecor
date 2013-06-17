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
 * Created: Jun 14, 2013
 *
 */
class Expressdecor_Shipp_Block_Sales_Totals extends Mage_Sales_Block_Order_Totals
{
	/**
	 * Initialize order totals array
	 *
	 * @return Mage_Sales_Block_Order_Totals
	 */
	protected function _initTotals()
	{
		parent::_initTotals();
		$signature_enabled=Mage::getStoreConfig('sales/signature_shipping/enabled');
		$signature_value=$this->getSource()->getShippingAddress()->getSignatureRequired();
		 
		/**
		 * Add shipping signature
		 */ 	 
		if ($signature_enabled && $signature_value && !$this->getSource()->getIsVirtual() )
		{
			$newArray = array();
			foreach( $this->_totals as $key => $value ) {
				$newArray[ $key ] = $value;
				if(  $key == 'shipping' ) {					
					$newArray[ 'signature' ] =new Varien_Object(array(
							'code'      => 'signature',
							'value'     => $signature_value,
							'base_value'=> $signature_value,
							'label' => Mage::helper('shipp')->__('Signature at delivery'),
							'after'=>'shipping'
						));				 
				}							 			
			}
				
			$this->_totals=$newArray;
			 
		}
		 
		 return $this;
	}
}