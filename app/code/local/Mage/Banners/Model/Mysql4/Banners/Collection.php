<?php

class Mage_Banners_Model_Mysql4_Banners_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('banners/banners');
    }
	
	 public function addStoreFilter($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $store = array($store->getId());
        }

        $this->getSelect()->join(
            array('store_table' => $this->getTable('banners_store')),
            'main_table.banners_id = store_table.banners_id',
            array()
        )
        ->where('store_table.store_id in (?)', array(0, $store));

        return $this;
    }
	
}