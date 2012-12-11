<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Ebay extends Mage_Core_Helper_Abstract
{
    const NICK  = 'ebay';
    const TITLE = 'eBay';

    const MARKETPLACE_US     = 1;
    const MARKETPLACE_MOTORS = 9;

    // ########################################

    public function isEnabled()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/component/'.self::NICK.'/', 'mode');
    }

    public function isAllowed()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/component/'.self::NICK.'/', 'allowed');
    }

    public function isActive()
    {
        return $this->isEnabled() && $this->isAllowed();
    }

    public function isDefault()
    {
        return Mage::helper('M2ePro/Component')->getDefaultComponent() == self::NICK;
    }

    public function isObject($modelName, $value, $field = NULL)
    {
        $mode = Mage::helper('M2ePro/Component')->getComponentMode($modelName, $value, $field);
        return !is_null($mode) && $mode == self::NICK;
    }

    //-----------------------------------------

    public function getModel($modelName)
    {
        return Mage::helper('M2ePro/Component')->getComponentModel(self::NICK,$modelName);
    }

    public function getObject($modelName, $value, $field = NULL)
    {
        return Mage::helper('M2ePro/Component')->getComponentObject(self::NICK, $modelName, $value, $field);
    }

    public function getCollection($modelName)
    {
        return $this->getModel($modelName)->getCollection();
    }

    // ########################################

    public function getAccount($value, $field = NULL)
    {
        is_null($field) && $field = 'id';

        $cacheKey = self::NICK.'_ACCOUNT_DATA_'.$field.'_'.$value;
        $cacheData = Mage::helper('M2ePro')->getCacheValue($cacheKey);

        if ($cacheData === false) {
            $cacheData = $this->getObject('Account',$value,$field);
            Mage::helper('M2ePro')->setCacheValue($cacheKey,$cacheData,array(self::NICK),60*60*24);
        }

        return $cacheData;
    }

    public function getMarketplace($value, $field = NULL)
    {
        is_null($field) && $field = 'id';

        $cacheKey = self::NICK.'_MARKETPLACE_DATA_'.$field.'_'.$value;
        $cacheData = Mage::helper('M2ePro')->getCacheValue($cacheKey);

        if ($cacheData === false) {
            $cacheData = $this->getObject('Marketplace',$value,$field);
            Mage::helper('M2ePro')->setCacheValue($cacheKey,$cacheData,array(self::NICK),60*60*24);
        }

        return $cacheData;
    }

    // ########################################

    public function getItemUrl($ebayItemId,
                               $accountMode = Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION,
                               $marketplaceId = NULL)
    {
        $marketplaceId = (int)$marketplaceId;
        $marketplaceId <= 0 && $marketplaceId = self::MARKETPLACE_US;

        $domain = $this->getMarketplace($marketplaceId)->getUrl();
        if ($accountMode == Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX) {
            $domain = 'sandbox.'.$domain;
        }

        if ($marketplaceId != self::MARKETPLACE_MOTORS) {
            $domain = 'cgi.' . $domain;
        }

        return 'http://'.$domain.'/ws/eBayISAPI.dll?ViewItem&item='.(double)$ebayItemId;
    }

    public function getMemberUrl($ebayMemberId, $accountMode = Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION)
    {
        $domain = 'ebay.com';
        if ($accountMode == Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX) {
            $domain = 'sandbox.'.$domain;
        }
        return 'http://myworld.'.$domain.'/'.(string)$ebayMemberId;
    }

    // ########################################

    public function clearAllCache()
    {
        Mage::helper('M2ePro')->removeTagCacheValues(self::NICK);
    }

    public function getCarriers()
    {
        return array(
            'dhl'   => 'DHL',
            'fedex' => 'FedEx',
            'ups'   => 'UPS',
            'usps'  => 'USPS'
        );
    }

    // ########################################
}