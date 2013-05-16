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
 * Created: May 6, 2013
 *
 */
class Expressdecor_Inventoryupload_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function processfile($file) {
		 
		$product = Mage::getModel('catalog/product');
		$row = 0;
		if (($handle = fopen($file, "r")) !== FALSE) {
			  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			  			if ($row==0) {
			  				$row++;
			  				continue;
			  			}
			  			
					    $sku = trim($data[0]);
					    
					    $stock_status = (trim($data[1])=="Out of Stock"? 0:1 );
					    $stock_msg=trim($data[2]);
					    
					    $p = $product->loadByAttribute('sku',$sku);
					    if (!$p->getId()) 
					    	 throw new Exception("Product ".$sku." doesn't found.");
					    if ($p->getStatus()==0)
					    	throw new Exception("Product ".$sku." is disabled.");
					    
					    $stockItem=Mage::getModel('cataloginventory/stock_item')->loadByProduct($p->getId());
					    if (!$stockItem->getId())
					    		throw new Exception("Product stock status ".$sku." doesn't found. (Please check if manage stock options is enabled)");
					    					    		  					    
					    $stockItem->setData('is_in_stock', $stock_status);
					    $stockItem->setData('stock_message', $stock_msg)->save();
					    //Changed to stock item
					   // $p->setOutofstockMsg($stock_msg)->save();
					   
					    unset($stockItem);
					    unset($p);
			  }
		}
		fclose($handle);
	}
}
	  