<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Order_Item_Proxy
{
    /** @var $item Ess_M2ePro_Model_Ebay_Order_Item|Ess_M2ePro_Model_Amazon_Order_Item */
    protected $item = NULL;

    // ########################################

    public function __construct(Ess_M2ePro_Model_Component_Child_Abstract $item)
    {
        $this->item = $item;
    }

    // ########################################

    /**
     * Return product associated with order item
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->item->getParentObject()->getProduct();
    }

    // ########################################

    /**
     * Return variation information
     *
     * 'option_name' => 'option_value'
     *
     * @return array
     */
    public function getVariation()
    {
        return array();
    }

    /**
     * Return both options names and values in lower case
     *
     * @return array
     */
    public function getLowerCasedVariation()
    {
        $variation = $this->getVariation();

        if (empty($variation)) {
            return array();
        }

        $lowerCasedVariation = array();
        foreach ($variation as $optionName => $optionValue) {
            $lowerCasedVariation[trim(strtolower($optionName))] = trim(strtolower($optionValue));
        }

        return $lowerCasedVariation;
    }

    // ########################################

    /**
     * Return item purchase price
     *
     * @abstract
     * @return float
     */
    abstract public function getPrice();

    /**
     * Return item purchase qty
     *
     * @abstract
     * @return int
     */
    abstract public function getQty();

    /**
     * Return item tax rate
     *
     * @abstract
     * @return float
     */
    abstract public function getTaxRate();

    /**
     * Check whether item has Tax (not VAT)
     *
     * @abstract
     * @return bool
     */
    abstract public function hasTax();

    /**
     * Check whether item has VAT (value added tax)
     *
     * @abstract
     * @return bool
     */
    abstract public function hasVat();

    // ########################################
}