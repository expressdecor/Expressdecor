<?php
/**
 * Iceberg Commerce
 * @author     IcebergCommerce
 * @package    IcebergCommerce_WidgetTagProducts
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */
class IcebergCommerce_WidgetTagProducts_Model_Tags
{
	public function toOptionArray()
	{
		$tagModel = Mage::getModel('tag/tag');
		
		$collection = $tagModel->getCollection()
			->addStatusFilter( $tagModel->getApprovedStatus() )
			->addOrder('name', 'ASC');
            
		$ret = array();
		
		foreach( $collection as $tag )
		{
			$ret[] = array('label' => $tag->getName() , 'value' => $tag->getId() );
		}
        	
        return $ret;
	}
}
