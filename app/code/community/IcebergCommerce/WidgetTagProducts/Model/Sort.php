<?php
/**
 * Iceberg Commerce
 * @author     IcebergCommerce
 * @package    IcebergCommerce_WidgetTagProducts
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */
class IcebergCommerce_WidgetTagProducts_Model_Sort
{
	public function toOptionArray()
	{
		$ret = Mage::getSingleton('catalog/category')
            ->getAvailableSortByOptions();
            
        if (!$ret)
        {
        	return array();
        }
        	
        return $ret;
	}
}