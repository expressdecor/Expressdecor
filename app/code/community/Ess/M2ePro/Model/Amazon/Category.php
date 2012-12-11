<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Category extends Ess_M2ePro_Model_Component_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Category');
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        /* @var $writeConnection Varien_Db_Adapter_Pdo_Mysql */
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $listingProductTable = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_listing_product');

        $writeConnection->update(
            $listingProductTable,
            array('category_id' => null),
            array('category_id = ?' => $this->getId())
        );

        $this->deleteSpecifics();

        $this->delete();
        return true;
    }

    // ########################################

    public function getSpecifics()
    {
        return Mage::getModel('M2ePro/Amazon_Category_Specific')
            ->getCollection()
            ->addFieldToFilter('category_id',$this->getId())
            ->getData();
    }

    public function deleteSpecifics()
    {
        $specifics = $this->getRelatedSimpleItems('Amazon_Category_Specific','category_id',true);
        foreach ($specifics as $specific) {
            $specific->deleteInstance();
        }
    }

    // ########################################
}