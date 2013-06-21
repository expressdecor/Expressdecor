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
 * Created: Jun 13, 2013
 *
 */
class Expressdecor_Shipp_Model_Total_Signature extends Mage_Sales_Model_Quote_Address_Total_Abstract {  
	
    protected $_code = 'signature';
    
    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Expressdecor_Shipp_Model_Total_Signature
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        
        
        if ($address->getData('address_type')=='billing') return $this;         
 
        $quote = $address->getQuote();
 		$signature_enabled=Mage::getStoreConfig('sales/signature_shipping/enabled');
 		$signature_requred=$address->getSignatureRequired();
 		 
 		if (!$signature_requred) {
 			$address_quote=Mage::getModel("sales/quote_address")->load($address->getId());
 			$signature_requred=$address_quote->getSignatureRequired();
 		}
 		
 		$signature_apply=$address->getSignatureApply();
 		 
 		$signature_fee =Mage::getStoreConfig('sales/signature_shipping/amount');
 		$items = $this->_getAddressItems($address);
 		
   		$req = Mage::app()->getRequest();
   		$full_action_name=$req->getModuleName()."_".$req->getControllerName();//."_".$req->getActionName();
  
        if (     $signature_enabled && $signature_requred || $signature_enabled && $signature_apply && $full_action_name!="onestepcheckout_ajax" )  {     
        	 
        	//if (   $signature_apply) {
        	 
        		$address->setSignatureRequired($signature_fee)->save();
        		$signature_requred=$signature_fee;
        		$this->_setAmount($signature_fee);
        		$this->_setBaseAmount($signature_fee);
        //	}
         
        }        
                
        
        
        if (is_int($signature_requred) && $signature_requred==0 && $signature_enabled) {
        	 
        	$address->setSignatureRequired(0)->save();
        	$this->_setAmount(0);
        	$this->_setBaseAmount(0);
        }     
        
    }
 
  public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
    	$signature_enabled=Mage::getStoreConfig('sales/signature_shipping/enabled');
    	$signature_requred=$address->getSignatureRequired();
    	$signature_apply=$address->getSignatureApply();
    	$req = Mage::app()->getRequest();
    	$full_action_name=$req->getModuleName()."_".$req->getControllerName();//."_".$req->getActionName();
    	
    	  if (     $signature_enabled && $signature_requred|| $signature_enabled && $signature_apply && $full_action_name!="onestepcheckout_ajax"  )  {     
    		 
    		if ($address->getData('address_type')=='billing') return $this;
    		//if (   $signature_apply) {
        	$signature_fee =Mage::getStoreConfig('sales/signature_shipping/amount');
        	$address->addTotal(array(
            	    'code'=>$this->getCode(),
                	'title'=>Mage::helper('Shipping')->__('Signature at delivery'),
                	'value'=> $signature_fee  
        	));
    	//	}
    	}
        return $this;
    }
}