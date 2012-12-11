<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Magento extends Mage_Core_Helper_Abstract
{
    private $defaultWebsite = NULL;
    private $defaultStoreGroup = NULL;
    private $defaultStore = NULL;

    // ########################################

    public function getName()
    {
        return 'magento';
    }

    public function getVersion($asArray = false)
    {
        $versionString = Mage::getVersion();
        return $asArray ? explode('.',$versionString) : $versionString;
    }

    public function getRevision()
    {
        return 'undefined';
    }

    //----------------------------------------

    public function getLocale()
    {
        $localeComponents = explode('_' , Mage::app()->getLocale()->getLocale());
        return strtolower($localeComponents[0]);
    }

    // ########################################

    public function getEditionName()
    {
        if ($this->isProfessionalEdition()) {
            return 'professional';
        }
        if ($this->isEnterpriseEdition()) {
            return 'enterprise';
        }
        if ($this->isCommunityEdition()) {
            return 'community';
        }

        if ($this->isGoUsEdition()) {
            return 'magento go US';
        }
        if ($this->isGoUkEdition()) {
            return 'magento go UK';
        }
        if ($this->isGoAuEdition()) {
            return 'magento go AU';
        }

        if ($this->isGoEdition()) {
            return 'magento go';
        }

        return 'undefined';
    }

    //----------------------------------------

    public function isGoEdition()
    {
        return class_exists('Saas_Db',false);
    }

    public function isProfessionalEdition()
    {
        if ($this->isGoEdition()) {
            return false;
        }

        $modules = $this->getModules();
        if (in_array('Professional_License',$modules)) {
            return true;
        }

        return false;
    }

    public function isEnterpriseEdition()
    {
        if ($this->isGoEdition()) {
            return false;
        }

        $modules = $this->getModules();
        if (in_array('Enterprise_License',$modules)) {
            return true;
        }

        return false;
    }

    public function isCommunityEdition()
    {
        if ($this->isGoEdition()) {
            return false;
        }

        if ($this->isProfessionalEdition()) {
            return false;
        }

        if ($this->isEnterpriseEdition()) {
            return false;
        }

        return true;
    }

    //----------------------------------------

    public function isGoUsEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        $region = Mage::getConfig()->getOptions()->getTenantRegion();
        return strtolower($region) == 'en_us';
    }

    public function isGoUkEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        $region = Mage::getConfig()->getOptions()->getTenantRegion();
        return strtolower($region) == 'en_gb';
    }

    public function isGoAuEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        $region = Mage::getConfig()->getOptions()->getTenantRegion();
        return strtolower($region) == 'en_au';
    }

    //----------------------------------------

    public function isGoCustomEdition()
    {
        if (!$this->isGoEdition()) {
            return false;
        }

        return $this->isGoUsEdition() ||
               $this->isGoUkEdition() ||
               $this->isGoAuEdition();
    }

    // ########################################

    public function getMySqlTables()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read')->listTables();
    }

    public function getDatabaseTablesPrefix()
    {
        return (string)Mage::getConfig()->getTablePrefix();
    }

    public function getDatabaseName()
    {
        return (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');
    }

    // ########################################

    public function getModules()
    {
        return array_keys((array)Mage::getConfig()->getNode('modules')->children());
    }

    public function isTinyMceAvailable()
    {
        if ($this->isCommunityEdition()) {
            return version_compare($this->getVersion(false), '1.4.0.0', '>=');
        }
        return true;
    }

    public function getUrl($route, array $params = array())
    {
        return Mage::helper('M2ePro')->getGlobalValue('base_controller')->getUrl($route,$params);
    }

    public function getBaseCurrency()
    {
        return (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
    }

    //----------------------------------------

    public function isSecretKeyToUrl()
    {
        return (bool)Mage::getStoreConfigFlag('admin/security/use_form_key');
    }

    public function getCurrentSecretKey()
    {
        if (!$this->isSecretKeyToUrl()) {
            return '';
        }
        return Mage::getSingleton('adminhtml/url')->getSecretKey();
    }

    // ########################################

    public function getDefaultWebsite()
    {
        if (is_null($this->defaultWebsite)) {
            $this->defaultWebsite = Mage::getModel('core/website')->load(1,'is_default');
            if (is_null($this->defaultWebsite->getId())) {
                $this->defaultWebsite = Mage::getModel('core/website')->load(0);
                if (is_null($this->defaultWebsite->getId())) {
                    throw new Exception('Getting default website is failed');
                }
            }
        }
        return $this->defaultWebsite;
    }

    public function getDefaultStoreGroup()
    {
        if (is_null($this->defaultStoreGroup)) {

            $defaultWebsite = $this->getDefaultWebsite();
            $defaultStoreGroupId = $defaultWebsite->getDefaultGroupId();

            $this->defaultStoreGroup = Mage::getModel('core/store_group')->load($defaultStoreGroupId);
            if (is_null($this->defaultStoreGroup->getId())) {
                $this->defaultStoreGroup = Mage::getModel('core/store_group')->load(0);
                if (is_null($this->defaultStoreGroup->getId())) {
                    throw new Exception('Getting default store group is failed');
                }
            }
        }
        return $this->defaultStoreGroup;
    }

    public function getDefaultStore()
    {
        if (is_null($this->defaultStore)) {

            $defaultStoreGroup = $this->getDefaultStoreGroup();
            $defaultStoreId = $defaultStoreGroup->getDefaultStoreId();

            $this->defaultStore = Mage::getModel('core/store')->load($defaultStoreId);
            if (is_null($this->defaultStore->getId())) {
                $this->defaultStore = Mage::getModel('core/store')->load(0);
                if (is_null($this->defaultStore->getId())) {
                    throw new Exception('Getting default store is failed');
                }
            }
        }
        return $this->defaultStore;
    }

    //------------------------------------------

    public function getDefaultWebsiteId()
    {
        return (int)$this->getDefaultWebsite()->getId();
    }

    public function getDefaultStoreGroupId()
    {
        return (int)$this->getDefaultStoreGroup()->getId();
    }

    public function getDefaultStoreId()
    {
        return (int)$this->getDefaultStore()->getId();
    }

    //------------------------------------------

    public function isSingleStoreMode()
    {
        return Mage::getModel('core/store')->getCollection()->getSize() <= 2;
    }

    public function isMultiStoreMode()
    {
        return !$this->isSingleStoreMode();
    }

    // ########################################

    public function getAttributeSets()
    {
        $temp = Mage::getModel('eav/entity_attribute_set')
                        ->getCollection()
                        ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                        ->setOrder('attribute_set_name', 'ASC')
                        ->toArray();
        return $temp['items'];
    }

    public function getAttributesByAttributeSetId($attributeSetId)
    {
        $attributeSetId = (int)$attributeSetId;

        $attributesTemp = Mage::getModel('eav/entity_attribute')->getCollection();
        $attributesTemp->setEntityTypeFilter(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId())
                       ->setAttributeSetFilter($attributeSetId);

        $attributes = array();
        foreach ($attributesTemp as $attributeTemp) {
            if ((int)$attributeTemp->getData('is_visible') != 1) {
                continue;
            }
            $attributes[] = array(
                'label' => $attributeTemp->getData('frontend_label'),
                'code'  => $attributeTemp->getData('attribute_code')
            );
        }

        return $attributes;
    }

    public function getAttributesByAttributeSets(array $attributeSets = array())
    {
        if (count($attributeSets) == 0) {
            return array();
        }

        $attributes = array();
        foreach ($attributeSets as $attributeSetId) {

            $attributesTemp = $this->getAttributesByAttributeSetId($attributeSetId);

            if (count($attributesTemp) == 0) {
                continue;
            }

            if (count($attributes) == 0) {
                $attributes = $attributesTemp;
                continue;
            }

            $intersectAttributes = array();
            foreach ($attributesTemp as $attributeTemp) {
                $findValue = false;
                foreach ($attributes as $attribute) {
                    if ($attributeTemp['label'] == $attribute['label'] &&
                        $attributeTemp['code'] == $attribute['code']) {
                        $findValue = true;
                        break;
                    }
                }
                if ($findValue) {
                    $intersectAttributes[] = $attributeTemp;
                }
            }

            $attributes = $intersectAttributes;
        }

        sort($attributes);

        return $attributes;
    }

    public function getAttributesByAllAttributeSets()
    {
        $attributeSets = $this->getAttributeSets();

        $attributeSetsIds = array();
        foreach ($attributeSets as $attributeSet) {
            $attributeSetsIds[] = $attributeSet['attribute_set_id'];
        }

        $attributes = $this->getAttributesByAttributeSets($attributeSetsIds);

        return (array)$attributes;
    }

    // ########################################

    public function clearCache()
    {
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_ALL);
    }

    // ########################################
}