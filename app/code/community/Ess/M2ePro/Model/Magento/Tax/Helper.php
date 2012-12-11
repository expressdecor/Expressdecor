<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Tax_Helper
{
    public function hasRatesForCountry($countryId)
    {
        return Mage::getModel('tax/calculation_rate')
            ->getCollection()
            ->addFieldToFilter('tax_country_id', $countryId)
            ->addFieldToFilter('code', array('neq' => Ess_M2ePro_Model_Magento_Tax_Rule_Builder::TAX_RATE_CODE))
            ->addFieldToFilter('code', array('neq' => 'eBay Tax Rate')) // backward compatibility with m2e 3.x.x
            ->getSize();
    }
}