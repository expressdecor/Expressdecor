<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Connector_Product_HelperVariations
{
    // ########################################

    public function getRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if ($listingProduct->getMagentoProduct()->isProductWithoutVariations()) {
            return array();
        }

        $requestData = array();

        // TODO next release

        return $requestData;
    }

    // ########################################

    public function updateAfterAction(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if ($listingProduct->getMagentoProduct()->isProductWithoutVariations()) {
            return;
        }

        // TODO next release
    }

    // ########################################
}