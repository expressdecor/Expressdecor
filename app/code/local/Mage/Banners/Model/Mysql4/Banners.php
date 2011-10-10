<?php

class Mage_Banners_Model_Mysql4_Banners extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the banners_id refers to the key field in your database table.
        $this->_init('banners/banners', 'banners_id');
    }
	
	
	 protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
    	
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('banners_store'))
            ->where('banners_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row['store_id'];
            }
            $object->setData('store_id', $storesArray);
        }

        return parent::_afterLoad($object);
        
    }
	
	/**
     * Process page data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
		
        $condition = $this->_getWriteAdapter()->quoteInto('banners_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('banners_store'), $condition);
    
        foreach ((array)$object->getData('stores') as $store) {
            $storeArray = array();
            $storeArray['banners_id'] = $object->getId();
            $storeArray['store_id'] = $store;
            $this->_getWriteAdapter()->insert($this->getTable('banners_store'), $storeArray);
        }
    
        return parent::_afterSave($object);
        
    }

	
}