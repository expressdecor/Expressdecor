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
 * Created: May 21, 2013
 *
 */
class Expressdecor_Inventoryupload_Model_Resource_Category_Indexer_Product extends Mage_Catalog_Model_Resource_Category_Indexer_Product {
	/**
	 * Rebuild index for anchor categories and associated to child categories products
	 *
	 * @param null | array $categoryIds
	 * @param null | array $productIds
	 * @return Mage_Catalog_Model_Resource_Category_Indexer_Product
	 */
	protected function _refreshAnchorRelations($categoryIds = null, $productIds = null)
	{
		if (!$categoryIds && !$productIds) {
			return $this;
		}
	
		$anchorInfo     = $this->_getAnchorAttributeInfo();
		$visibilityInfo = $this->_getVisibilityAttributeInfo();
		$statusInfo     = $this->_getStatusAttributeInfo();
	
		/**
		 * Insert anchor categories relations
		*/
		$adapter = $this->_getReadAdapter();
		$isParent = $adapter->getCheckSql('MIN(cp.category_id)=ce.entity_id', 1, 0);
		//Alex Position
		/*$position = 'MIN('.
				$adapter->getCheckSql(
						'cp.category_id = ce.entity_id',
						'cp.position',
						'(cc.position + 1) * ('.$adapter->quoteIdentifier('cc.level').' + 1) * 10000 + cp.position'
				)
				.')';*/
		$position="cp.position";
		//Alex Position
		$select = $adapter->select()
		->distinct(true)
		->from(array('ce' => $this->_categoryTable), array('entity_id'))
		->joinInner(
				array('cc' => $this->_categoryTable),
				$adapter->quoteIdentifier('cc.path') .
				' LIKE ('.$adapter->getConcatSql(array($adapter->quoteIdentifier('ce.path'),$adapter->quote('/%'))).')'
				. ' OR cc.entity_id=ce.entity_id'
				, array()
		)
		->joinInner(
				array('cp' => $this->_categoryProductTable),
				'cp.category_id=cc.entity_id',
				array('cp.product_id', 'position' => $position, 'is_parent' => $isParent)
		)
		->joinInner(array('pw' => $this->_productWebsiteTable), 'pw.product_id=cp.product_id', array())
		->joinInner(array('g'  => $this->_groupTable), 'g.website_id=pw.website_id', array())
		->joinInner(array('s'  => $this->_storeTable), 's.group_id=g.group_id', array('store_id'))
		->joinInner(array('rc' => $this->_categoryTable), 'rc.entity_id=g.root_category_id', array())
		->joinLeft(
				array('dca'=>$anchorInfo['table']),
				"dca.entity_id=ce.entity_id AND dca.attribute_id={$anchorInfo['id']} AND dca.store_id=0",
				array())
				->joinLeft(
						array('sca'=>$anchorInfo['table']),
						"sca.entity_id=ce.entity_id AND sca.attribute_id={$anchorInfo['id']} AND sca.store_id=s.store_id",
						array())
						->joinLeft(
								array('dv'=>$visibilityInfo['table']),
								"dv.entity_id=pw.product_id AND dv.attribute_id={$visibilityInfo['id']} AND dv.store_id=0",
								array())
								->joinLeft(
										array('sv'=>$visibilityInfo['table']),
										"sv.entity_id=pw.product_id AND sv.attribute_id={$visibilityInfo['id']} AND sv.store_id=s.store_id",
										array('visibility' => $adapter->getCheckSql(
												'MIN(sv.value_id) IS NOT NULL',
												'MIN(sv.value)', 'MIN(dv.value)'
										))
		)
		->joinLeft(
				array('ds'=>$statusInfo['table']),
				"ds.entity_id=pw.product_id AND ds.attribute_id={$statusInfo['id']} AND ds.store_id=0",
				array())
				->joinLeft(
						array('ss'=>$statusInfo['table']),
						"ss.entity_id=pw.product_id AND ss.attribute_id={$statusInfo['id']} AND ss.store_id=s.store_id",
						array())
						/**
						 * Condition for anchor or root category (all products should be assigned to root)
		*/
		->where('('.
				$adapter->quoteIdentifier('ce.path') . ' LIKE ' .
				$adapter->getConcatSql(array($adapter->quoteIdentifier('rc.path'), $adapter->quote('/%'))) . ' AND ' .
				$adapter->getCheckSql('sca.value_id IS NOT NULL',
						$adapter->quoteIdentifier('sca.value'),
						$adapter->quoteIdentifier('dca.value')) . '=1) OR ce.entity_id=rc.entity_id'
		)
		->where(
				$adapter->getCheckSql('ss.value_id IS NOT NULL', 'ss.value', 'ds.value') . '=?',
				Mage_Catalog_Model_Product_Status::STATUS_ENABLED
		)
		->group(array('ce.entity_id', 'cp.product_id', 's.store_id'));
		if ($categoryIds) {
			$select->where('ce.entity_id IN (?)', $categoryIds);
		}
		if ($productIds) {
			$select->where('pw.product_id IN(?)', $productIds);
		}
	
		$sql = $select->insertFromSelect($this->getMainTable());
		$this->_getWriteAdapter()->query($sql); 		
		 
		return $this;
	}
	
	/**
	 * Rebuild all index data
	 *
	 * @return Mage_Catalog_Model_Resource_Category_Indexer_Product
	 */
	public function reindexAll()
	{
		$this->useIdxTable(true);
		$this->beginTransaction();
		
		
		
		try {
			$this->clearTemporaryIndexTable();
			$idxTable = $this->getIdxTable();
			$idxAdapter = $this->_getIndexAdapter();
			$stores = $this->_getStoresInfo();			
			
			/**
			 * Build index for each store
			*/
			foreach ($stores as $storeData) {
				$storeId    = $storeData['store_id'];
				$websiteId  = $storeData['website_id'];
				$rootPath   = $storeData['root_path'];
				$rootId     = $storeData['root_id'];
				/**
				 * Prepare visibility for all enabled store products
				 */
				$enabledTable = $this->_prepareEnabledProductsVisibility($websiteId, $storeId);
				/**
				 * Select information about anchor categories
				*/
				$anchorTable = $this->_prepareAnchorCategories($storeId, $rootPath);
				/**
				 * Add relations between not anchor categories and products
				*/
				$select = $idxAdapter->select();
				/** @var $select Varien_Db_Select */
				$select->from(
						array('cp' => $this->_categoryProductTable),
						array('category_id', 'product_id', 'position', 'is_parent' => new Zend_Db_Expr('1'),
								'store_id' => new Zend_Db_Expr($storeId))
				)
				->joinInner(array('pv' => $enabledTable), 'pv.product_id=cp.product_id', array('visibility'))
				->joinLeft(array('ac' => $anchorTable), 'ac.category_id=cp.category_id', array())
				->where('ac.category_id IS NULL');
	
				$query = $select->insertFromSelect(
						$idxTable,
						array('category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'),
						false
				);
				 
				$idxAdapter->query($query);
	
				/**
				 * Assign products not associated to any category to root category in index
				*/
	
				$select = $idxAdapter->select();
				$select->from(
						array('pv' => $enabledTable),
						array(new Zend_Db_Expr($rootId), 'product_id', new Zend_Db_Expr('0') , new Zend_Db_Expr('1'),
								new Zend_Db_Expr($storeId), 'visibility')
				)
				->joinLeft(array('cp' => $this->_categoryProductTable), 'pv.product_id=cp.product_id', array())
				->where('cp.product_id IS NULL');
				 
				$query = $select->insertFromSelect(
						$idxTable,
						array('category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'),
						false
				);
				$idxAdapter->query($query);
	
				/**
				 * Prepare anchor categories products
				*/
				$anchorProductsTable = $this->_getAnchorCategoriesProductsTemporaryTable();
				$idxAdapter->delete($anchorProductsTable);
				//Alex Position
				/*$position = 'MIN('.
						$idxAdapter->getCheckSql(
								'ca.category_id = ce.entity_id',
								$idxAdapter->quoteIdentifier('cp.position'),
								'('.$idxAdapter->quoteIdentifier('ce.position').' + 1) * '
								.'('.$idxAdapter->quoteIdentifier('ce.level').' + 1 * 10000)'
								.' + '.$idxAdapter->quoteIdentifier('cp.position')
						)
						.')';*/
				$position="cp.position";												 							
				//Alex Position
				 
				$select = $idxAdapter->select()
				->useStraightJoin(true)
				->distinct(true)
				->from(array('ca' => $anchorTable), array('category_id'))
				->joinInner(
						array('ce' => $this->_categoryTable),
						$idxAdapter->quoteIdentifier('ce.path') . ' LIKE ' .
						$idxAdapter->quoteIdentifier('ca.path') . ' OR ce.entity_id = ca.category_id',
						array()
				)
				->joinInner(
						array('cp' => $this->_categoryProductTable),
						'cp.category_id = ce.entity_id',
						array('product_id')
				)
				->joinInner(
						array('pv' => $enabledTable),
						'pv.product_id = cp.product_id',
						array('position' => $position)
				)
				->group(array('ca.category_id', 'cp.product_id'));
							 
				$query = $select->insertFromSelect($anchorProductsTable,
						array('category_id', 'product_id', 'position'), false);
				$idxAdapter->query($query);
												
				/**
				 * Add anchor categories products to index
				*/
				$select = $idxAdapter->select()
				->from(
						array('ap' => $anchorProductsTable),
						array('category_id', 'product_id',
								'position' =>'IF (LENGTH(cp.position)>0, cp.position, -10)',//Alex => new Zend_Db_Expr('MIN('. $idxAdapter->quoteIdentifier('ap.position').')'), 
								'is_parent' => $idxAdapter->getCheckSql('cp.product_id > 0', 1, 0),
								'store_id' => new Zend_Db_Expr($storeId))
				)				 
				->joinLeft(
						array('cp' => $this->_categoryProductTable),
						'cp.category_id=ap.category_id AND cp.product_id=ap.product_id',
						array()
				)
				->joinInner(array('pv' => $enabledTable), 'pv.product_id = ap.product_id', array('visibility'));				
				$query = $select->insertFromSelect(
						$idxTable,
						array('category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'),
						false
				);
			 				 
				$idxAdapter->query($query); 			
				
				$select = $idxAdapter->select()
				->from(array('e' => $this->getTable('catalog/product')), null)
				->join(
						array('ei' => $enabledTable),
						'ei.product_id = e.entity_id',
						array())
						->joinLeft(
								array('i' => $idxTable),
								'i.product_id = e.entity_id AND i.category_id = :category_id AND i.store_id = :store_id',
								array())
								->where('i.product_id IS NULL')
								->columns(array(
										'category_id'   => new Zend_Db_Expr($rootId),
										'product_id'    => 'e.entity_id',
										'position'      =>  new Zend_Db_Expr('0'),
										'is_parent'     => new Zend_Db_Expr('1'),
										'store_id'      => new Zend_Db_Expr($storeId),
										'visibility'    => 'ei.visibility'
								));
	
								$query = $select->insertFromSelect(
										$idxTable,
										array('category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'),
										false
								);
	
								$idxAdapter->query($query, array('store_id' => $storeId, 'category_id' => $rootId));																
			}
	
			$this->syncData();
	
			/**
			 * Clean up temporary tables
			*/
			$this->clearTemporaryIndexTable();
			$idxAdapter->delete($enabledTable);
			$idxAdapter->delete($anchorTable);
			$idxAdapter->delete($anchorProductsTable);
			$this->commit();
			
			/** Alex Update queires for positions **/
			//Alex
			$select = $idxAdapter->select();
			$select->joinInner(
					array('cp' =>  $this->getTable('catalog/category_product')),
					$idxAdapter->quoteIdentifier('cpi.is_parent') . ' = 0 AND ' .
					$idxAdapter->quoteIdentifier('cpi.category_id') . ' > 1 AND ' .
					$idxAdapter->quoteIdentifier('cp.product_id') . '  = '.$idxAdapter->quoteIdentifier('cpi.product_id'),
					array('position'=>'cp.position')
			)
			->joinInner(
					array('ce' =>  $this->getTable('catalog/category')),
					$idxAdapter->quoteIdentifier('ce.path') . ' LIKE CONCAT("%/",cpi.category_id,"/%") AND ' .
					$idxAdapter->quoteIdentifier('ce.path') . 'LIKE  CONCAT("%/",cp.category_id) ' ,
					array()
			);
			$query = $idxAdapter->updateFromSelect($select,array("cpi" =>$this->getTable('catalog/category_product_index')));
			 
			$idxAdapter->query($query);
			//Alex
			/** Create table **/
				
			$this->_getWriteAdapter()->delete($this->getTable('inventoryupload/productposition'));
			$total_ordered=new Zend_Db_Expr('CAST(SUM('.$idxAdapter->quoteIdentifier('sfoi.qty_ordered').') AS SIGNED)  total_ordered');
			$position_table=$this->getTable('inventoryupload/productposition');
			$select = $idxAdapter->select();
				
			$select->from(
					array('sfoi' => $this->getTable('sales/order_item')),
					array('product_id', 'name',   $total_ordered)
			)
			->joinInner(
					array('so' =>  $this->getTable('sales/order')),
					$idxAdapter->quoteIdentifier('so.store_id') . ' = 1 AND ' .
					$idxAdapter->quoteIdentifier('sfoi.order_id') . '  = '.$idxAdapter->quoteIdentifier('so.entity_id'),
					array()
			)
			->joinInner(
					array('ccp' =>  $this->getTable('catalog/category_product')),
					$idxAdapter->quoteIdentifier('ccp.product_id') . '  = '.$idxAdapter->quoteIdentifier('sfoi.product_id'),
					array('position')
			)
			->joinInner(
					array('cpw' =>  $this->getTable('catalog/product_website')),
					$idxAdapter->quoteIdentifier('cpw.website_id') . ' = 1 AND ' .
					$idxAdapter->quoteIdentifier('cpw.product_id') . '  = '.$idxAdapter->quoteIdentifier('ccp.product_id'),
					array()
			)
			->group(array('product_id'))
			->order(array('total_ordered DESC'));
			
			$query = $select->insertFromSelect($position_table,
					array('product_id','name','total_ordered','position'), false);
			$idxAdapter->query($query);
			/** Create table **/
			// Update Positions for back to stock items			
			$select = $idxAdapter->select();
			$select
			->joinInner(
					array('csi' =>  $this->getTable('cataloginventory/stock_item')),
					$idxAdapter->quoteIdentifier('ccp.position') . ' = "-10" AND ' .
					$idxAdapter->quoteIdentifier('csi.is_in_stock') . ' = 1 AND ' .
					$idxAdapter->quoteIdentifier('csi.product_id') . '  = '.$idxAdapter->quoteIdentifier('ccp.product_id'),
					array()
			)
			->joinLeft(
					array('ccpp' =>  $position_table),
					$idxAdapter->quoteIdentifier('ccpp.product_id') . '  = '.$idxAdapter->quoteIdentifier('ccp.product_id'),
					array("position"=>new Zend_Db_Expr('IF (LENGTH(`ccpp`.`total_ordered`)>0,`ccpp`.`total_ordered`, 0 )') )
			);
			$query = $idxAdapter->updateFromSelect($select,array("ccp" =>$this->getTable('catalog/category_product')));
			//echo $query; die();
			$idxAdapter->query($query);
							 
			$query = $idxAdapter->updateFromSelect($select,array("ccp" =>$this->getTable('catalog/category_product_index')));
			$idxAdapter->query($query);
			//echo $query; die();
			// Update Positions for back to stock items
			/*Tags*/
			$select = $idxAdapter->select();			
			$select->joinInner(
					array('csi' =>  $this->getTable('cataloginventory/stock_item')),
					$idxAdapter->quoteIdentifier('tr.position') . ' = "-10" AND ' .
					$idxAdapter->quoteIdentifier('csi.is_in_stock') . ' = 1 AND ' .
					$idxAdapter->quoteIdentifier('csi.product_id') . '  = '.$idxAdapter->quoteIdentifier('tr.product_id'),
					array("position"=>new Zend_Db_Expr('"0"'))
			);				
			$query = $idxAdapter->updateFromSelect($select,array("tr" =>$this->getTable('tag/relation')));
			$idxAdapter->query($query);						
			$select = $idxAdapter->select();			
			$select->joinInner(
					array('csi' =>  $this->getTable('cataloginventory/stock_item')),
					$idxAdapter->quoteIdentifier('tr.position') . ' = "-10" AND ' .
					$idxAdapter->quoteIdentifier('csi.is_in_stock') . ' = 1 AND ' .
					$idxAdapter->quoteIdentifier('csi.product_id') . '  = '.$idxAdapter->quoteIdentifier('tr.product_id'),
					array()
			)
			->joinInner(
					array('ccpp' =>  $position_table),
					$idxAdapter->quoteIdentifier('ccpp.product_id') . '  = '.$idxAdapter->quoteIdentifier('tr.product_id'),
					array("position"=>'ccpp.total_ordered')
			);				
			$query = $idxAdapter->updateFromSelect($select,array("tr" =>$this->getTable('tag/relation')));
			 
			$idxAdapter->query($query);
			/*Tags*/
			/*set to 0*/
			/* $select = $idxAdapter->select();
				
			$select
			->joinInner(
					array('csi' =>  $this->getTable('cataloginventory/stock_item')),
					$idxAdapter->quoteIdentifier('ccp.position') . ' = "-10" AND ' .
					$idxAdapter->quoteIdentifier('csi.is_in_stock') . ' = 1 AND ' .
					$idxAdapter->quoteIdentifier('csi.product_id') . '  = '.$idxAdapter->quoteIdentifier('ccp.product_id'),
					array("position"=>new Zend_Db_Expr('"0"'))
			);			
			$query = $idxAdapter->updateFromSelect($select,array("ccp" =>$this->getTable('catalog/category_product')));
			$idxAdapter->query($query);
				
			//$select = $idxAdapter->select();
			
			/*$select
			->joinInner(
					array('csi' =>  $this->getTable('cataloginventory/stock_item')),
					$idxAdapter->quoteIdentifier('ccp.position') . ' = "-10" AND ' .
					$idxAdapter->quoteIdentifier('csi.is_in_stock') . ' = 1 AND ' .
					$idxAdapter->quoteIdentifier('csi.product_id') . '  = '.$idxAdapter->quoteIdentifier('ccp.product_id'),
					array("position"=>new Zend_Db_Expr('"0"'))
			);			*/ 
			//$query = $idxAdapter->updateFromSelect($select,array("ccp" =>$this->getTable('catalog/category_product_index')));
			//$idxAdapter->query($query);
			/*set to 0*/
			/*Move out of stock items*/

			$select = $idxAdapter->select();			
			$select->joinInner(
					array('csi' =>  $this->getTable('cataloginventory/stock_item')),
					$idxAdapter->quoteIdentifier('csi.is_in_stock') . ' = 0 AND ' .
					$idxAdapter->quoteIdentifier('csi.product_id') . '  = '.$idxAdapter->quoteIdentifier('ccp.product_id'),
					array("position"=>new Zend_Db_Expr('"-10"'))
			);
				
			$query = $idxAdapter->updateFromSelect($select,array("ccp" =>$this->getTable('catalog/category_product')));
			$idxAdapter->query($query);			 
			 		
			$query = $idxAdapter->updateFromSelect($select,array("ccp" =>$this->getTable('catalog/category_product_index')));				
			$idxAdapter->query($query);
			//tags			
			$select = $idxAdapter->select();			
			$select->joinInner(
					array('csi' =>  $this->getTable('cataloginventory/stock_item')),
					$idxAdapter->quoteIdentifier('csi.is_in_stock') . ' = 0 AND ' .
					$idxAdapter->quoteIdentifier('csi.product_id') . '  = '.$idxAdapter->quoteIdentifier('tr.product_id'),
					array("position"=>new Zend_Db_Expr('"-10"'))
			);				
			$query = $idxAdapter->updateFromSelect($select,array("tr" =>$this->getTable('tag/relation')));
			$idxAdapter->query($query);
			//tags
			//Clean temporary table
			//$this->_getWriteAdapter()->delete($this->getTable('inventoryupload/productposition'));
			/**Alex Update queries for positions**/
			
			
		} catch (Exception $e) {
			$this->rollBack();
			throw $e;
		}
		return $this;
	}
}