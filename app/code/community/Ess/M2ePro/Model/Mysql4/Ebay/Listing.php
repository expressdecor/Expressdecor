<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Listing extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing', 'listing_id');
        $this->_isPkAutoIncrement = false;
    }

    public function updateStatisticColumns()
    {
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        $dbSelect = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingProductTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`listing_id`")
                             ->where("`status` = ?",(int)Ess_M2ePro_Model_Listing_Product::STATUS_SOLD);

        $query = "UPDATE `{$this->getMainTable()}`
                  SET `products_sold_count` =  (".$dbSelect->__toString().")";

        $this->_getWriteAdapter()->query($query);
    }
}