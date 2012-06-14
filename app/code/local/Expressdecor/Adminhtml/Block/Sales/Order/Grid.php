<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Adminhtml sales orders grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Expressdecor_Adminhtml_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{

	protected function _prepareCollection()
	{
		
		/*->join(
		'sales/order_item',
		'`sales/order_item`.order_id=`main_table`.entity_id',
		array(
		'skus'  => new Zend_Db_Expr('group_concat(`sales/order_item`.sku SEPARATOR ",")'),
		'names' => new Zend_Db_Expr('group_concat(`sales/order_item`.name SEPARATOR ",")'),
		)
		);*/
//  		$collection->getSelect()->group('entity_id');

		$collection = Mage::getResourceModel($this->_getCollectionClass());
		$collection->getSelect()->joininner(array('sfop'=>'sales_flat_order_payment'),'`main_table`.`entity_id`=`sfop`.`parent_id`', array('payment_method'=>'sfop.method'));

		$this->setCollection($collection);
		return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
	}
	 

	protected function _prepareColumns()
	{
		parent::_prepareColumns();

/*
		$this->addColumn('names', array(
		'header'    => Mage::helper('Sales')->__('Name'),
		'width'     => '800px',
		'index'     => 'names',
		'type'        => 'text',
		));


		$this->addColumn('skus', array(
		'header'    => Mage::helper('Sales')->__('Skus'),
		'width'     => '80px',
		'index'     => 'skus',
		'type'        => 'text',
		));
		

		$this->addColumn('name',
		array(
		'header'=> Mage::helper('catalog')->__('Name'),
		'index' => 'name',
		'width' => '350px'
		));
*/

		$this->addColumn('outofstock_msg',
		array(
		'header'=> Mage::helper('catalog')->__('Out of Stock'),
		'width' => '150px',
		'type'  => 'text',
		'index' => 'outofstock_msg',
		));
		
		$this->addColumn('sales_flag',array(
		'header' => Mage::helper('sales')->__('Flag'),
		'index'  => 'sales_flag',
		'type'  => 'options',
		'width' => '70px',
		'options'   => array('Non US Add'=> 'Non US Add','cc Add/Non US Add' => 'cc Add/Non US Add','cc Add'=> 'cc Add',''=>'No flag','App Non US Add'=>'App Non US Add','App cc Add/Non US Add'=>'App cc Add/Non US Add','App cc Add'=>'App cc Add')
		));

		$this->addColumn('payment_method', array(
		'header'    => Mage::helper('sales')->__('Payment Method'),
		'index'     => 'payment_method',
		'type'  => 'options',
		'filter_index' => '`sfop`.`method`',
		'width' => '70px',
		'options'   => array('verisign' => 'Credit Card', 'checkmo' => 'Check/Money Order', 'paypal_express'=>'PayPal', 'googlecheckout'=>'Google Checkout')
		));

		$this->addColumn('channel', array(
		'header'    => Mage::helper('sales')->__('Channel'),
		'index'     => 'channel',
		'type'  => 'options',
		'width' => '70px',
		'options'   => array('Amazon' => 'Amazon', 'Sears' => 'Sears', 'Ebay'=>'Ebay', 'Ebayrefurb'=>'Ebay Refurb')
		));

				$this->addColumn('Promo code', array(
		'header'    => Mage::helper('sales')->__('Source'),
		'index'     => 'promo_code',
		'type'  => 'text',
		'width' => '20px'
		));
		
				$this->addColumn('Marketplace ID', array(
		'header'    => Mage::helper('sales')->__('Marketplace ID'),
		'index'     => 'foreign_system_id',
		'type'  => 'text',
		'width' => '20px'
		));

		//		return parent::_prepareColumns();
	}





}
