<?php
class Brandammo_Pronav_Model_Pronav extends Mage_Core_Model_Abstract
{
   private $_topLevelItems = array();
   public function _construct ()
   {
      parent::_construct();
      $this->_init('pronav/pronav');
   }
   
   public function getItemsData ($storeId)
   {
      $resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('core_read');
		$pronavTable = $resource->getTableName('pronav');
			$select = $read->select()
			   ->from($pronavTable, array('*'))
			   ->where('status = ?',1)
			   ->where('store_id = ?', 0)
			   ->orWhere('store_id = ?', $storeId)
			   ->order('index ASC') ;
      return $read->fetchAll($select);
   }
   /**
    * Currently not in use. Invoke getItemsData instead
    *
    * @param int $storeId
    * @return array
    */
   public function getTopLevelItems ($storeId)
   {
      $resource = Mage::getSingleton('core/resource');
		$read = $resource->getConnection('core_read');
		$pronavTable = $resource->getTableName('pronav');
			$select = $read->select()
			   ->where('status = ?',1)
			   ->from($pronavTable, array('name'))
			   ->where('store_id = ?', 0)
			   ->orWhere('store_id = ?', $storeId)
			   ->order('index ASC') ;
      return $read->fetchAll($select);
   }
   
   public function getBlockIdentifierByBlockId ($id)
   {
      $resource = Mage::getSingleton('core/resource');
      $read = $resource->getConnection('core_read');
      $select = $read->select()->from($resource->getTableName('cms_block'), array(
         'block_id','identifier'))->where('block_id = ?', $id);
      $result = $read->fetchRow($select);
      return array_key_exists('identifier', $result) ? $result['identifier'] : '';
   }
   
   /**
    * Retrieve current store model
    *
    * @return Mage_Core_Model_Store
    */
   public function getCurrentStore ()
   {
      return Mage::app()->getStore();
   }
}