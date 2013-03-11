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
 * Created: Mar 8, 2013
 *
 */
class Expressdecor_Searchredirects_Model_Indexer_Search extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * @var array
     */
    protected $_matchedEntities = array();

  
    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('searchredirects')->__('Search Redirects Data');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('searchredirects')->__('Rebuild Search Redirects Data');
    }

 
    public function reindexAll()
    {
    	 
    	return $this->buildredirects();
    }
    
    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {}

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
      //  $this->callEventHandler($event);
    }
    
    /**
     * @throws Exception
     * @return Expressdecor_Searchredirects_Model_Indexer_Search
     */
    public function buildredirects()
    {
    	//foreach store  
    	try {
    		$stores=Mage::app()->getStores();
    		foreach ($stores as $store) {
    			$storeid=$store->getId();
    			/*Products*/
    			$product_collection = Mage::getModel('catalog/product')->setStoreId($storeid)->getCollection();
    			$product_collection->addAttributeToSelect(array('name','sku','url_key'));
    			$product_collection->addAttributeToFilter('status', array('value'=>1));
    			$product_collection->addAttributeToFilter('visibility', array('value'=>4));
    			 
    			foreach ($product_collection as $product) {
    				$product=Mage::getModel('catalog/product')->setStoreId($storeid)->load($product->getId());
    				 
    				$url="/".$product->getUrlKey().".html";
    				$sku=str_replace("_Base","",$product->getSku());
    				$query_length=Mage::getModel('catalogsearch/query')->getMaxQueryLength();
    				$name=$product->getName();
    				if (strlen($name)>$query_length)
    						$name=substr($name,0,$query_length);
    				
    				$catalogsearch_model=Mage::getModel('catalogsearch/query')->getCollection()->addFieldToFilter('query_text',  $sku)->addFieldToFilter('store_id',  $storeid);    				    				    			 
    				if (count ($catalogsearch_model->getData())==0)   {
    					$this->upsert_redirect($sku,$url,$storeid);    					 
    				}else { //If there  redirect    					 
    					foreach ($catalogsearch_model  as $catalogsearch_item) {    			 
    						if ($catalogsearch_item->getRedirect() ==''){
    							$this->upsert_redirect($sku,$url,$storeid,$catalogsearch_item->getQueryId());    							    							 
    						}
    					}
    				}
    			 	//Name redirect
    				$catalogsearch_model=Mage::getModel('catalogsearch/query')->getCollection()->addFieldToFilter('query_text',  $name)->addFieldToFilter('store_id',  $storeid);
    				if (count ($catalogsearch_model->getData())==0) {
    					$this->upsert_redirect($name,$url,$storeid);    					 
    				}else { //If there  redirect    					 
    					foreach ($catalogsearch_model as $catalogsearch_item) {
    						if ($catalogsearch_item->getRedirect() ==''){    						 
    							$this->upsert_redirect($name,$url,$storeid,$catalogsearch_item->getQueryId());    							    							 
    						}
    					}
    				} 
    				 			    				
    			}
    			
    			/*Categories*/
    			$categories_collection= Mage::getModel('catalog/category')->getCollection()->setStoreId($storeid);
    			$categories_collection->addAttributeToSelect('*');
    			$categories_collection->addFieldToFilter('is_active', array('eq'=>'1'));
    			$categories_collection->addFieldToFilter('name', array('neq'=>'ed root'));    
    			
    			foreach ($categories_collection as $category ) {
    				
    				$category= Mage::getModel('catalog/category')->setStoreId($storeid)->load($category->getId());    				
    				$query_length=Mage::getModel('catalogsearch/query')->getMaxQueryLength();
    				$name=$category->getName();
    				if (strlen($name)>$query_length)
    					$name=substr($name,0,$query_length);
    				$url=str_replace("index.php","",$category->getUrl());
    				$url=str_replace(Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),"",$url);
    			     				 
    				$catalogsearch_model=Mage::getModel('catalogsearch/query')->getCollection()->addFieldToFilter('query_text',  $name)->addFieldToFilter('store_id',  $storeid);
    				if (count ($catalogsearch_model->getData())==0)   {
    					$this->upsert_redirect($name,$url,$storeid);
    				}else { //If there  redirect
    					foreach ($catalogsearch_model  as $catalogsearch_item) {
    						if ($catalogsearch_item->getRedirect() ==''){
    							$this->upsert_redirect($name,$url,$storeid,$catalogsearch_item->getQueryId());
    						}
    					}
    				}
    				 			 
    			}
 
    		} //Stores Foreach
 
    	} catch (Exception $e) {
    		$this->rollBack();
    		throw $e;
    	}
    
    	return $this;
    }
    
 
    /**
     * @param unknown $query_text
     * @param unknown $redirect
     * @param unknown $StoreId
     * @param string $query_id
     */
    private function upsert_redirect($query_text,$redirect,$StoreId,$query_id=null){
    	$new_redirect=Mage::getModel('catalogsearch/query');
    	 
    	$data= array('query_text'=>$query_text,
    			'store_id'=>$StoreId,
    			'redirect'=>$redirect,
    			'num_results'=>1,
    			'popularity'=>1,
    			'is_active'=>1);
    	$new_redirect->setData($data);
    	    	 
    	if ($query_id){
    		$new_redirect->load($query_id);
    		$new_redirect->setRedirect($redirect);    		
    	}
     	$new_redirect->save();         	 
    	unset ($new_redirect);
    }
}
