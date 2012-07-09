<?php
/**
 * Iceberg Commerce
 * @author     IcebergCommerce
 * @package    IcebergCommerce_WidgetTagProducts
 * @copyright  Copyright (c) 2010 Iceberg Commerce
 */
class IcebergCommerce_WidgetTagProducts_Block_List extends Mage_Catalog_Block_Product_List implements Mage_Widget_Block_Interface
{
	/**
	 * Initialize Widget List
	 * We have to define some defaults for grid columns 
	 * this is usually done throught lay xml, but we want to minimize footprint of widget
	 */
	public function _construct()
	{
		parent::_construct();

		$this->setTemplate('catalog/product/list.phtml');

		// Default Column Counts For Widget
		$this->addColumnCountLayoutDepend('one_column',        5)
		->addColumnCountLayoutDepend('two_columns_left',  4)
		->addColumnCountLayoutDepend('two_columns_right', 4)
		->addColumnCountLayoutDepend('three_columns',     3);
	}


	/**
	 * Get Product Collection for Widget List
	 * This sets category id
	 * Also sets all filterable attribute values
	 */
	protected function _getProductCollection()
	{
		if (is_null($this->_productCollection))
		{
			$layer = $this->getLayer();
			
		
			$tagIds = explode(',' , $this->getProductTagIds() );

			/* @todo add current category or default 2 */
			$catid=$_REQUEST['catId'];
			If (!empty($catid)) {
				$layer->setCurrentCategory($catid);
			}

			$this->_productCollection = $layer->getProductCollection()
			->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
			->addAttributeToSelect('name')
			->addStoreFilter(Mage::app()->getStore()->getId())
			->addUrlRewrite();

			
			$i=0;
			foreach ($tagIds as $tagid) {
				if( $i==0) {
					$this->_productCollection->getSelect()
					->join( array('relation' =>'tag_relation'), 'relation.product_id = e.entity_id and relation.tag_id = '.$tagid);
				} else {
					$this->_productCollection->getSelect()
					->join( array('relation'.$i =>'tag_relation'), 'relation'.$i.'.product_id = e.entity_id and relation'.$i.'.tag_id = '.$tagid);
				}
				$i++;
			}

			$this->_productCollection->getSelect()
			->join( array('t'=>'tag'), 't.tag_id = relation.tag_id', array());


			Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_productCollection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInSiteFilterToCollection($this->_productCollection);
			$this->_productCollection->getSelect()->where('relation.active = (?)',1);
			//sorting
			$order = $this->getRequest()->getParam('order');
			if (!$order) {
				$this->_productCollection->getSelect()->order('relation.position DESC');
			} else {
				$sort_direction=$this->getRequest()->getParam('dir');
				if ($order=='position') {
					$this->_productCollection->getSelect()->order('relation.position '.$sort_direction);
				} elseif ($order=='name') {
					$this->_productCollection->getSelect()
					->join( array('ev'=>'catalog_product_entity_varchar'), 'ev.entity_id = e.entity_id', array())
					->where('ev.attribute_id = (?)',60)
				 	->order('ev.value '.$sort_direction);
				} else{
					$this->_productCollection->getSelect()->order('ev.'.$order.' '.$sort_direction);
				}				 
			}
			 
			//$this->_productCollection->getSelect()->where('relation.active = (?)',1)->order('relation.position DESC');
			
			$this->_productCollection->setFlag('distinct', true);
			
			//echo $this->_productCollection->getSelect(); 
		}
		//end

		$limitSize = (int) $this->getOptionLimitSize();

		if ($limitSize > 0)
		{
			$this->_productCollection->setPageSize($limitSize);
		}
		return $this->_productCollection;


	}


	/**
	 * Overrides parent definition of this method
	 * so that we can use our custom flags to control pagination parameters
	 * and to also add a pager block
	 */
	public function getToolbarBlock()
	{
		$toolbarBlock = null;
		$blockName    = $this->getToolbarBlockName();

		// Rand is used in case more than one instance of widget on same page... to insure unique block.
		$pagerBlock   = $this->getLayout()->createBlock('page/html_pager', microtime() . '-' . rand(0,100000));

		if ($blockName)
		{
			$toolbarBlock = $this->getLayout()->getBlock($blockName);
		}

		if (!$toolbarBlock)
		{
			// Rand is used in case more than one instance of widget on same page... to insure unique block.
			$toolbarBlock = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime() . '-' . rand(0,100000));
		}

		// --------------------------------------------
		// Set Defaults for Pagination
		// --------------------------------------------
		$pageSize = (int) $this->getOptionPageSize();
		if ($pageSize > 0)
		{
			$toolbarBlock->setData('_current_limit', $pageSize);
		}

		$showAll = (int) $this->getOptionShowAll();
		if ($showAll > 0)
		{
			$toolbarBlock->setData('_current_limit', 'all');
		}

		$columnCount = (int) $this->getOptionColumnCount();
		if ($columnCount > 0)
		{
			$this->setColumnCount($columnCount);
		}

		// Default Order
		$sort = $this->getOptionSortBy();
		if( $sort ) {
			$toolbarBlock->setDefaultOrder( $sort );
			if ($this->isToolbarHidden())
			{
				$toolbarBlock->setData('_current_grid_order', $sort);
			}
		}

		// Default Direction
		$dir = $this->getOptionSortDirection();
		if( $dir )
		{
			$toolbarBlock->setDefaultDirection( $dir );
			if ($this->isToolbarHidden())
			{
				$toolbarBlock->setData('_current_grid_direction', $dir);
			}
		}

		// Default Mode
		// - for paginated, show what user wants
		// - for no toolbar, only show grid mode
		if ($this->isToolbarHidden())
		{
			$toolbarBlock->setData('_current_grid_mode', 'grid');
		}


		$toolbarBlock->setChild('product_list_toolbar_pager', $pagerBlock);
		return $toolbarBlock;
	}

	/**
	 * Extend parent logic to add the ability to turn off toolbar
	 */
	public function getToolbarHtml()
	{
		if ($this->isToolbarHidden())
		{
			return null;
		}

		return parent::getToolbarHtml();
	}

	/**
	 * Helper
	 */
	private function isToolbarHidden()
	{
		return ($this->getOptionShowAll() || $this->getOptionLimitSize());
	}

}
