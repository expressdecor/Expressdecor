<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Updater extends
                                                                    Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    // ########################################

    public function __construct()
    {
        $this->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
    }

    // ########################################

    public function updateVariations(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // TODO next release

        return;
    }

    public function isAddedNewVariationsAttributes(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // TODO next release

        return;
    }

    // ########################################

    protected function validateChannelConditions($sourceVariations)
    {
        return $sourceVariations;
    }

    // ########################################
}