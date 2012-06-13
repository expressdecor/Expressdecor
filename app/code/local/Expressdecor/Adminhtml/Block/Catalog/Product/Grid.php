<?php

class Expressdecor_Adminhtml_Block_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{


	protected function _prepareColumns()
	{


		$this->addColumn('name',
		array(
		'header'=> Mage::helper('catalog')->__('Name'),
		'index' => 'name',
		'width' => '350px'
		));

          	parent::_prepareColumns();
		$this->addColumn('outofstock_msg',
		array(
		'header'=> Mage::helper('catalog')->__('Out of Stock'),
		'width' => '150px',
		'type'  => 'text',
		'index' => 'outofstock_msg',
		));


	//	return parent::_prepareColumns();
	}

}
