<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Order_Item_Proxy extends Ess_M2ePro_Model_Order_Item_Proxy
{
    // ########################################

    public function getPrice()
    {
        return $this->item->getPrice() + $this->item->getGiftPrice();
    }

    public function getQty()
    {
        return $this->item->getQtyPurchased();
    }

    public function getTaxRate()
    {
        $amazonOrder = $this->item->getParentObject()->getOrder()->getChildObject();

        return round($amazonOrder->getTaxAmount() / $amazonOrder->getPaidAmount(), 2);
    }

    public function hasVat()
    {
        return false;
    }

    public function hasTax()
    {
        return false;
    }

    // ########################################
}