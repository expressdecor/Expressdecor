<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Listing extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    public function _construct()
    {
        $this->_init('M2ePro/Listing', 'id');
    }

    public function updateStatisticColumns()
    {
        $listingProductTable = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();

        $dbSelect1 = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingProductTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`id`");

        $dbSelect2 = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingProductTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`id`")
                             ->where("`status` = ?",(int)Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $dbSelect3 = $this->_getWriteAdapter()
                             ->select()
                             ->from($listingProductTable,new Zend_Db_Expr('COUNT(*)'))
                             ->where("`listing_id` = `{$this->getMainTable()}`.`id`")
                             ->where("`status` != ?",(int)Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);

        $query = "UPDATE `{$this->getMainTable()}`
                  SET `products_total_count` = (".$dbSelect1->__toString()."),
                      `products_listed_count` = (".$dbSelect2->__toString()."),
                      `products_inactive_count` = (".$dbSelect3->__toString().")";

        $this->_getWriteAdapter()->query($query);
    }

    public function getListingsWhereIsProduct($productId)
    {
        $listingProductTable = Mage::getResourceModel(
            'M2ePro/Listing_Product'
        )->getMainTable();
        $listingProductVariationTable = Mage::getResourceModel(
            'M2ePro/Listing_Product_Variation'
        )->getMainTable();
        $listingProductVariationOptionTable = Mage::getResourceModel(
            'M2ePro/Listing_Product_Variation_Option'
        )->getMainTable();

        $dbSelect = $this->_getWriteAdapter()
            ->select()
            ->from(array('l' => $this->getMainTable()),new Zend_Db_Expr('DISTINCT `l`.`id`, `l`.`component_mode`'))
            ->join(
                array('lp' => $listingProductTable),
                '`l`.`id` = `lp`.`listing_id`',
                array()
            )
            ->joinLeft(
                array('lpv' => $listingProductVariationTable),
                '`lp`.`id` = `lpv`.`listing_product_id`',
                array()
            )
            ->joinLeft(
                array('lpvo' => $listingProductVariationOptionTable),
                '`lpv`.`id` = `lpvo`.`listing_product_variation_id`',
                array()
            )
            ->where("`lp`.`product_id` = ?",(int)$productId)
            ->orWhere("`lpvo`.`product_id` IS NOT NULL AND `lpvo`.`product_id` = ?",(int)$productId);

        $newData = array();
        $oldData = $this->_getWriteAdapter()->fetchAll($dbSelect);

        $listingsIds = array();
        foreach ($oldData as $item) {
            if (in_array($item['id'],$listingsIds)) {
                continue;
            }
            $newData[] = $item;
        }

        return $newData;
    }
}