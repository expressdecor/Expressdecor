<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Connector_Product_Helper
{
    // ########################################

    public function getListRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $requestData = $this->getReviseRequestData($listingProduct,$params);

        if (empty($requestData['sku'])) {

            $tempSku = $listingProduct->getChildObject()->getAddingSku();

            if (empty($tempSku)) {
                throw new Exception('You must specify SKU if you want to add new inventory item.');
            }

            $requestData['sku'] = $tempSku;
        }

        $productId = NULL;
        $productIdType = NULL;

        $tempGeneralId = $listingProduct->getChildObject()->getGeneralId();
        empty($tempGeneralId) && $tempGeneralId = $listingProduct->getChildObject()->getAddingGeneralId();

        if (!empty($tempGeneralId)) {

            $requestData['product_id'] = $tempGeneralId;
            $requestData['product_id_type'] = Mage::helper('M2ePro/Component_Amazon')->isASIN($tempGeneralId) ?
                                              'ASIN' : 'ISBN';

        } else {

            $tempWorldwideId = $listingProduct->getChildObject()->getWorldwideId();
            empty($tempWorldwideId) && $tempWorldwideId = $listingProduct->getChildObject()->getAddingWorldwideId();

            if (empty($tempWorldwideId)) {
                throw new Exception(
                    'You must specify Global Product Identifier if you want to add new inventory item.'
                );
            }

            $requestData['product_id'] = $tempWorldwideId;
            $requestData['product_id_type'] = Mage::helper('M2ePro/Component_Amazon')->isUPC($tempWorldwideId) ?
                                              'UPC' : 'EAN';
        }

        return $requestData;
    }

    public function updateAfterListAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          array $nativeRequestData = array(), array $params = array())
    {
        // Add New Amazon Item
        //---------------------
        $this->createNewAmazonItem($listingProduct,$nativeRequestData['sku']);
        //---------------------

        // Save additional info
        //---------------------
        $dataForUpdate = array(
            'sku' => $nativeRequestData['sku']
        );

        if (in_array($nativeRequestData['product_id_type'],array('ASIN','ISBN'))) {

            $dataForUpdate['general_id'] = $nativeRequestData['product_id'];

            $isIsbnGeneralId = (int)Mage::helper('M2ePro/Component_Amazon')->isISBN($nativeRequestData['product_id']);
            $dataForUpdate['is_isbn_general_id'] = $isIsbnGeneralId;

        } else if (in_array($nativeRequestData['product_id_type'],array('UPC','EAN'))) {

            $dataForUpdate['worldwide_id'] = $nativeRequestData['product_id'];

            $isUpcWorldwideId = (int)Mage::helper('M2ePro/Component_Amazon')->isUPC($nativeRequestData['product_id']);
            $dataForUpdate['is_upc_worldwide_id'] = $isUpcWorldwideId;
        }

        $listingProduct->addData($dataForUpdate)->save();
        //---------------------

        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $nativeRequestData,
                                        $params,
                                        Mage::helper('M2ePro')->getCurrentGmtDate());
        //---------------------

        // Update Variations
        //---------------------
        Mage::getModel('M2ePro/Amazon_Connector_Product_HelperVariations')
                   ->updateAfterAction($listingProduct);
        //---------------------
    }

    //----------------------------------------

    public function getRelistRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        return $this->getReviseRequestData($listingProduct,$params);
    }

    public function updateAfterRelistAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                            array $nativeRequestData = array(), array $params = array())
    {
        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $nativeRequestData,
                                        $params,
                                        Mage::helper('M2ePro')->getCurrentGmtDate());
        //---------------------

        // Update Variations
        //---------------------
        Mage::getModel('M2ePro/Amazon_Connector_Product_HelperVariations')
                   ->updateAfterAction($listingProduct);
        //---------------------
    }

    //----------------------------------------

    public function getReviseRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        // Set permissions
        //-----------------
        $permissions = array(
            'general'=>true,
            'variations'=>true,
            'qty'=>true,
            'price'=>true
        );

        if (isset($params['only_data'])) {
            foreach ($permissions as &$value) {
                $value = false;
            }
            $permissions = array_merge($permissions,$params['only_data']);
        }

        if (isset($params['all_data'])) {
            foreach ($permissions as &$value) {
                $value = true;
            }
        }
        //-----------------

        $requestData = array();

        // Prepare Variations
        //-------------------
        Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Updater')->updateVariations($listingProduct);
        $tempVariations = Mage::getModel('M2ePro/Amazon_Connector_Product_HelperVariations')
                                                ->getRequestData($listingProduct);

        $requestData['is_variation_item'] = false;
        if (is_array($tempVariations) && count($tempVariations) > 0) {
            $requestData['is_variation_item'] = true;
        }
        //-------------------

        // Get Variations
        //-------------------
        if ($permissions['variations'] && $requestData['is_variation_item']) {
            $requestData['variation'] = $tempVariations;
        }
        //-------------------

        // Get Amazon SKU Info
        //-------------------
        $requestData['sku'] = $listingProduct->getChildObject()->getSku();
        //-------------------

        // Get Main Data
        //-------------------
        $permissions['general'] && $this->addDetailsData($listingProduct,$requestData);

        if ($permissions['qty'] && !$requestData['is_variation_item']) {
            $this->addQtyData($listingProduct,$requestData);
        }

        if ($permissions['price'] && !$requestData['is_variation_item']) {
            $this->addPriceData($listingProduct,$requestData);
        }
        //-------------------

        return $requestData;
    }

    public function updateAfterReviseAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                            array $nativeRequestData = array(), array $params = array())
    {
        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $nativeRequestData,
                                        $params);
        //---------------------

        // Update Variations
        //---------------------
        Mage::getModel('M2ePro/Amazon_Connector_Product_HelperVariations')
                   ->updateAfterAction($listingProduct);
        //---------------------
    }

    //----------------------------------------

    public function getStopRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $requestData = array();

        // Get Amazon SKU Info
        //-------------------
        $requestData['sku'] = $listingProduct->getChildObject()->getSku();
        //-------------------

        // Get Main Data
        //-------------------
        $requestData['qty'] = 0;
        //-------------------

        return $requestData;
    }

    public function updateAfterStopAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          array $nativeRequestData = array(), array $params = array())
    {
        // Update Listing Product
        //---------------------
        $dataForUpdate = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
            'end_date' => Mage::helper('M2ePro')->getCurrentGmtDate(),
            'online_qty' => 0
        );

        isset($params['status_changer']) && $dataForUpdate['status_changer'] = (int)$params['status_changer'];

        $listingProduct->addData($dataForUpdate)->save();
        //---------------------

        // Update Variations
        //---------------------
        Mage::getModel('M2ePro/Amazon_Connector_Product_HelperVariations')
                   ->updateAfterAction($listingProduct);
        //---------------------
    }

    //----------------------------------------

    public function getDeleteRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $requestData = array();

        // Get Amazon SKU Info
        //-------------------
        $requestData['sku'] = $listingProduct->getChildObject()->getSku();
        //-------------------

        return $requestData;
    }

    public function updateAfterDeleteAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                            array $nativeRequestData = array(), array $params = array())
    {
        // Update Listing Product
        //---------------------
        $dataForUpdate = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            'end_date' => Mage::helper('M2ePro')->getCurrentGmtDate(),
            'online_qty' => 0
        );

        isset($params['status_changer']) && $dataForUpdate['status_changer'] = (int)$params['status_changer'];

        $listingProduct->addData($dataForUpdate)->save();
        //---------------------

        // Update Variations
        //---------------------
        Mage::getModel('M2ePro/Amazon_Connector_Product_HelperVariations')
                   ->updateAfterAction($listingProduct);
        //---------------------
    }

    // ########################################

    protected function addQtyData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['qty'] = $listingProduct->getChildObject()->getQty();

        $requestData['handling_time'] = $listingProduct->getChildObject()->getHandlingTime();
        if (empty($requestData['handling_time'])) {
            unset($requestData['handling_time']);
        }

        $requestData['restock_date'] = $listingProduct->getChildObject()->getRestockDate();
        if (empty($requestData['restock_date'])) {
            unset($requestData['restock_date']);
        }
    }

    protected function addPriceData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['price'] = $listingProduct->getChildObject()->getPrice();
        $requestData['currency'] = $listingProduct->getSellingFormatTemplate()->getChildObject()->getCurrency();

        $requestData['sale_price'] = $listingProduct->getChildObject()->getSalePrice();

        if (is_null($requestData['sale_price'])) {
            unset($requestData['sale_price']);
        } else if ($requestData['sale_price'] > 0) {
            $requestData['sale_price_start_date'] = $listingProduct->getChildObject()->getSalePriceStartDate();
            $requestData['sale_price_end_date'] = $listingProduct->getChildObject()->getSalePriceEndDate();
        }
    }

    protected function addDetailsData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['condition'] = $listingProduct->getChildObject()->getCondition();

        if (is_null($requestData['condition'])) {
            unset($requestData['condition']);
        } else {
            $requestData['condition_note'] = $listingProduct->getChildObject()->getConditionNote();
            if (is_null($requestData['condition_note'])) {
                unset($requestData['condition_note']);
            }
        }
    }

    // ########################################

    protected function createNewAmazonItem(Ess_M2ePro_Model_Listing_Product $listingProduct, $sku)
    {
        $dataForAdd = array(
            'account_id' => (int)$listingProduct->getListing()->getGeneralTemplate()->getAccountId(),
            'marketplace_id' => (int)$listingProduct->getListing()->getGeneralTemplate()->getMarketplaceId(),
            'sku' => $sku,
            'product_id' =>(int)$listingProduct->getProductId(),
            'store_id' => (int)$listingProduct->getListing()->getStoreId()
        );

        return Mage::getModel('M2ePro/Amazon_Item')->setData($dataForAdd)->save()->getId();
    }

    protected function updateProductAfterAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                array $nativeRequestData = array(),
                                                array $params = array(),
                                                $startDate = false)
    {
        $dataForUpdate = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            'is_afn_channel' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_NO
        );

        isset($params['status_changer']) && $dataForUpdate['status_changer'] = (int)$params['status_changer'];
        $startDate !== false && $dataForUpdate['start_date'] = $startDate;

        if (isset($nativeRequestData['qty'])) {

            $dataForUpdate['online_qty'] = (int)$nativeRequestData['qty'];

            if ((int)$dataForUpdate['online_qty'] > 0) {
                $dataForUpdate['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
                $dataForUpdate['end_date'] = NULL;
            } else {
                $dataForUpdate['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
                $dataForUpdate['end_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
            }
        }

        if (isset($nativeRequestData['price'])) {
            $dataForUpdate['online_price'] = (float)$nativeRequestData['price'];
        }

        $listingProduct->addData($dataForUpdate)->save();
    }

    // ########################################
}