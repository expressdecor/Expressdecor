<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Order_ShippingAddress extends Varien_Object
{
    /** @var Ess_M2ePro_Model_Order */
    protected $order;

    abstract public function getRawData();

    public function __construct(Ess_M2ePro_Model_Order $order)
    {
        $this->order = $order;
    }

    public function isValid()
    {
        $street = array_filter($this->getData('street'));

        if ($this->getData('city') == '' || count($street) == 0) {
            return false;
        }

        if (!$this->isCountryValid() || !$this->isRegionValid()) {
            return false;
        }

        return true;
    }

    private function isCountryValid()
    {
        try {
            $country = Mage::getModel('directory/country')->loadByCode($this->getData('country_code'));
        } catch (Exception $e) {
            return false;
        }

        return !is_null($country->getId());
    }

    private function isRegionValid()
    {
        if (!$this->isCountryValid()) {
            return false;
        }

        $country = Mage::getModel('directory/country')->loadByCode($this->getData('country_code'));
        $countryRegions = $country->getRegionCollection();

        if ($countryRegions->getSize() == 0) {
            return true;
        }

        $regionByCode = $countryRegions->getItemByColumnValue('code', $this->getData('state'));
        $regionByName = $countryRegions->getItemByColumnValue('default_name', $this->getData('state'));

        return !is_null($regionByCode) || !is_null($regionByName);
    }

    public function getCountryName()
    {
        if (!$this->isCountryValid()) {
            return $this->getData('country_code');
        }

        return Mage::getModel('directory/country')->loadByCode($this->getData('country_code'))->getName();
    }

    public function getRegionId()
    {
        if (!$this->isRegionValid()) {
            return 1;
        }

        $country = Mage::getModel('directory/country')->loadByCode($this->getData('country_code'));
        $countryRegions = $country->getRegionCollection();
        $countryRegions->getSelect()->where('code = ? OR default_name = ?', $this->getData('state'));

        $region = $countryRegions->getFirstItem();

        return $region->getId() ? $region->getId() : 1;
    }
}