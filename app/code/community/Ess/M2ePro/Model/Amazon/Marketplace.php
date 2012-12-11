<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Marketplace extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Marketplace');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        $accounts = Mage::getModel('M2ePro/Amazon_Account')->getCollection()->getItems();
        foreach ($accounts as $account) {
            /** @var $account Ess_M2ePro_Model_Amazon_Account */
            if ($account->isExistMarketplaceItem($this->getId())) {
                return true;
            }
        }

        return false;
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $categoriesTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category');
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($categoriesTable,array('marketplace_id = ?'=>$this->getId()));

        $marketplacesTable  = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_marketplace');
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete($marketplacesTable,array('marketplace_id = ?'=>$this->getId()));

        $items = $this->getRelatedSimpleItems('Amazon_Item','marketplace_id',true);
        foreach ($items as $item) {
            $item->deleteInstance();
        }

        $categories = $this->getRelatedSimpleItems('Amazon_Category','marketplace_id',true);
        foreach ($categories as $category) {
            $category->deleteInstance();
        }

        $this->delete();
        return true;
    }

    // ########################################

    public function getDeveloperKey()
    {
        return $this->getData('developer_key');
    }

    public function getDefaultCurrency()
    {
        return $this->getData('default_currency');
    }

    // ########################################
}