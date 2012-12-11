<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater extends
                                                                  Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    // ########################################

    public function __construct()
    {
        $this->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
    }

    // ########################################

    public function updateVariations(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->getChildObject()->isListingTypeFixed() ||
            !$listingProduct->getGeneralTemplate()->getChildObject()->isVariationMode()) {
            return;
        }

        $variations = parent::updateVariations($listingProduct);
        $this->saveVariationsSets($listingProduct,$variations);
    }

    public function isAddedNewVariationsAttributes(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->getChildObject()->isListingTypeFixed() ||
            !$listingProduct->getGeneralTemplate()->getChildObject()->isVariationMode()) {
            return false;
        }

        return parent::isAddedNewVariationsAttributes($listingProduct);
    }

    // ########################################

    protected function validateChannelConditions($sourceVariations)
    {
        $failResult = array(
            'set' => array(),
            'variations' => array()
        );

        $set = $sourceVariations['set'];
        $variations = $sourceVariations['variations'];

        foreach ($set as $singleSet) {
            if (count($singleSet) > 30) {
                // Maximum 30 options by one attribute:
                // Color: Red, Blue, Green, ...
                return $failResult;
            }
        }

        foreach ($variations as $singleVariation) {
            if (count($singleVariation) > 5) {
                // Max 5 pair attribute-option:
                // Color: Blue, Size: XL, ...
                return $failResult;
            }
        }

        if (count($variations) > 120) {
            // Not more that 120 possible variations
            return $failResult;
        }

        return $sourceVariations;
    }

    private function saveVariationsSets(Ess_M2ePro_Model_Listing_Product $listingProduct,$variations)
    {
        if (!isset($variations['set'])) {
            return;
        }

        $additionalData = $listingProduct->getChildObject()->getData('additional_data');
        $additionalData = is_null($additionalData)
                          ? array()
                          : json_decode($additionalData,true);

        $additionalData['variations_sets'] = $variations['set'];

        $listingProduct->getChildObject()
                       ->setData('additional_data',json_encode($additionalData))
                       ->save();
    }

    // ########################################
}