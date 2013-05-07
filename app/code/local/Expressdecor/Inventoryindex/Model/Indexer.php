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
 * Created: May 7, 2013
 *
 */
class Expressdecor_Inventoryindex_Model_Indexer extends Mage_Index_Model_Indexer_Abstract
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
		return Mage::helper('inventoryindex')->__('Configurable products stock');
	}
	
	/**
	 * Retrieve Indexer description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return Mage::helper('inventoryindex')->__('Rebuild Configurable products stock');
	}
	
	
	public function reindexAll()
	{
	
		return $this->buildstock();
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
	
	public function buildstock()
	{		 
		try {			 
				$product_collection = Mage::getModel('catalog/product')->getCollection();
				$product_collection ->addAttributeToFilter('status', array('value'=>1))
									->addAttributeToFilter('type_id', array('eq' => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE))
									->addAttributeToFilter('visibility', array('value'=>4));
	
				foreach ($product_collection as $product) {
					$childProducts = Mage::getModel('catalog/product_type_configurable')
					->getUsedProducts(null,$product);
				 	$stock_status=0;	
				 				  
				 	foreach ($childProducts as $childproduct) {				 		
				 		if ($childproduct->getStatus()) {
				 			$stock_status+=$childproduct->getIsInStock();				
				 		}				 		 
				 	}
				 	
				 	$stockItem=Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
				 	
				 	if ($stockItem->getIsInStock() && !$stock_status) {
				 		$stockItem->setIsInStock(0)->save();
				 	} elseif (!$stockItem->getIsInStock() && $stock_status ) {
				 		$stockItem->setIsInStock(1)->save();
				 	}				  
				}	
		} catch (Exception $e) {
			$this->rollBack();
			throw $e;
		}
	
		return $this;
	}
	
}