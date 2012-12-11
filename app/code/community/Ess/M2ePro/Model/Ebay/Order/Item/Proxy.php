<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Order_Item_Proxy extends Ess_M2ePro_Model_Order_Item_Proxy
{
    // ########################################

    public function getVariation()
    {
        return $this->item->getVariation();
    }

    public function getPrice()
    {
        return $this->item->getPrice();
    }

    public function getQty()
    {
        return $this->item->getQtyPurchased();
    }

    public function getTaxRate()
    {
        return $this->item->getEbayOrder()->getTaxRate();
    }

    public function hasTax()
    {
        return $this->item->getEbayOrder()->hasTax();
    }

    public function hasVat()
    {
        return $this->item->getEbayOrder()->hasVat();
    }

    // ########################################
}