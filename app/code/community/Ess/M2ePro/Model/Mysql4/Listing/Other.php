<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Listing_Other extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    public function _construct()
    {
        $this->_init('M2ePro/Listing_Other', 'id');
    }

    public function getItemsWhereIsProduct($productId)
    {
        $dbSelect = $this->_getWriteAdapter()
            ->select()
            ->from(array('lo' => $this->getMainTable()),array('id','component_mode'))
            ->where("`lo`.`product_id` IS NOT NULL AND `lo`.`product_id` = ?",(int)$productId);

        $newData = array();
        $oldData = $this->_getWriteAdapter()->fetchAll($dbSelect);

        $itemsIds = array();
        foreach ($oldData as $item) {
            if (in_array($item['id'],$itemsIds)) {
                continue;
            }
            $newData[] = $item;
        }

        return $newData;
    }
}