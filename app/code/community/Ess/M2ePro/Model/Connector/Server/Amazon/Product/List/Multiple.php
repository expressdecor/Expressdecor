<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Amazon_Product_List_Multiple
    extends Ess_M2ePro_Model_Connector_Server_Amazon_Product_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','add','entities');
    }

    // ########################################

    protected function getActionIdentifier()
    {
        return 'list';
    }

    protected function getResponserModel()
    {
        return 'Amazon_Product_List_MultipleResponser';
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function prepareListingsProducts($listingsProducts)
    {
        $tempListingsProducts = array();

        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->isNotListed()) {

                // Parser hack -> Mage::helper('M2ePro')->__('Item is already on Amazon, or not available.');
                $this->addListingsProductsLogsMessage($listingProduct, 'Item is already on Amazon, or not available.',
                                                      Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

                continue;
            }

            if ($listingProduct->isLockedObject(NULL) ||
                $listingProduct->isLockedObject('in_action') ||
                $listingProduct->isLockedObject($this->getActionIdentifier().'_action')) {

                // ->__('Another action is being processed. Try again when the action is completed.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'Another action is being processed. Try again when the action is completed.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                continue;
            }

            $addingSku = $listingProduct->getChildObject()->getSku();
            empty($addingSku) && $addingSku = $listingProduct->getChildObject()->getAddingSku();

            if (empty($addingSku)) {

                // Parser hack -> Mage::helper('M2ePro')->__('SKU is not provided. Please, check Listing settings.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'SKU is not provided. Please, check Listing settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                continue;
            }

            if (strlen($addingSku) > 40) {

                // Parser hack -> Mage::helper('M2ePro')->__('The length of sku must be less than 40 characters.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct, 'The length of sku must be less than 40 characters.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                continue;
            }

            $tempListingsProducts[] = $listingProduct;
        }

        $tempListingsProducts2 = $this->checkOnlineSkuExistance($tempListingsProducts);

        $tempListingsProducts = array();

        foreach ($tempListingsProducts2 as $listingProduct) {

            if (!$this->checkGeneralConditions($listingProduct)) {
                continue;
            }

            $tempListingsProducts[] = $listingProduct;
        }

        return $tempListingsProducts;
    }

    // ########################################

    protected function getRequestData()
    {
        $requestData = array();

        $requestData['items'] = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $productVariations = $listingProduct->getVariations(true);

            foreach ($productVariations as $variation) {
                /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                $variation->deleteInstance();
            }

            $nativeData = Mage::getModel('M2ePro/Amazon_Connector_Product_Helper')
                                         ->getListRequestData($listingProduct,$this->params);

            $sendedData = $nativeData;
            $sendedData['id'] = $listingProduct->getId();

            $this->listingProductRequestsData[$listingProduct->getId()] = array(
                'native_data' => $nativeData,
                'sended_data' => $sendedData
            );

            $requestData['items'][] = $sendedData;
        }

        return $requestData;
    }

    // ########################################

    private function checkGeneralConditions(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $addingGeneralId = $listingProduct->getChildObject()->getGeneralId();

        if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER &&
            empty($addingGeneralId)) {

            $message  = 'You can list a product only with assigned ASIN. ';
            $message .= 'Please, use the Search ASIN tool:  ';
            $message .= 'press the icon in ASIN/ISBN column or choose appropriate command in the Actions dropdown.';
            $message .= ' Assigned ASIN will be displayed in ASIN/ISBN column.';

            $this->addListingsProductsLogsMessage($listingProduct, $message,
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        empty($addingGeneralId) && $addingGeneralId = $listingProduct->getChildObject()->getAddingGeneralId();

        if (!empty($addingGeneralId)) {

            $isAsin = Mage::helper('M2ePro/Component_Amazon')->isASIN($addingGeneralId);

            if (!$isAsin) {

                $isIsbn = Mage::helper('M2ePro/Component_Amazon')->isISBN($addingGeneralId);

                if (!$isIsbn) {

                    // ->__('ASIN/ISBN has a wrong format. Please, check its value in product settings.');
                    $this->addListingsProductsLogsMessage(
                        $listingProduct,
                        'ASIN/ISBN has a wrong format. Please, check its value in product settings.',
                        Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                    );

                    return false;
                }
            }

        } else {

            $addingWorldwideId = $listingProduct->getChildObject()->getWorldwideId();
            if (empty($addingWorldwideId)) {
                $addingWorldwideId = $listingProduct->getChildObject()->getAddingWorldwideId();
            }

            if (!empty($addingWorldwideId)) {

                $isUpc = Mage::helper('M2ePro/Component_Amazon')->isUPC($addingWorldwideId);

                if (!$isUpc) {

                    $isEan = Mage::helper('M2ePro/Component_Amazon')->isEAN($addingWorldwideId);

                    if (!$isEan) {

                        // ->__('UPC/EAN has a wrong format. Please, check its value in product settings.');
                        $this->addListingsProductsLogsMessage(
                            $listingProduct,
                            'UPC/EAN has a wrong format. Please, check its value in product settings.',
                            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                        );

                        return false;
                    }
                }

            } else {

                // ->__('ASIN/ISBN or UPC/EAN are not provided. Please, check Listing and product settings.');
                $this->addListingsProductsLogsMessage(
                    $listingProduct,
                    'ASIN/ISBN or UPC/EAN are not provided. Please, check Listing and product settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                return false;
            }
        }

        $addingCondition = $listingProduct->getChildObject()->getCondition();
        $validConditions = $listingProduct->getGeneralTemplate()->getChildObject()->getConditionValues();

        if (empty($addingCondition) || !in_array($addingCondition,$validConditions)) {

            // ->__('Condition is invalid or missed. Please, check Listing and product settings.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'Condition is invalid or missed. Please, check Listing and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        $addingConditionNote = $listingProduct->getChildObject()->getConditionNote();

        if (is_null($addingConditionNote)) {

            // ->__('Condition note is invalid or missed. Please, check Listing and product settings.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'Condition note is invalid or missed. Please, check Listing and product settings.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        if (!empty($addingConditionNote) && strlen($addingConditionNote) > 2000) {

            // ->__('The length of condition note must be less than 2000 characters.');
            $this->addListingsProductsLogsMessage(
                $listingProduct, 'The length of condition note must be less than 2000 characters.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        return true;
    }

    //-----------------------------------------

    private function checkOnlineSkuExistance($listingProducts)
    {
        $result = array();
        $listingProductsPacks = array_chunk($listingProducts,20,true);

        foreach ($listingProductsPacks as $listingProductsPack) {

            $skus = array();

            foreach ($listingProductsPack as $key => $listingProduct) {
                $skus[$key] = $listingProduct->getChildObject()->getAddingSku();
            }

            try {

                /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Server_Amazon_Dispatcher */
                $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector')->getDispatcher();
                $response = $dispatcherObject->processVirtualAbstract('product','search','asinBySku',
                    array('items' => $skus),'items', $this->marketplace->getId(), $this->account->getId());

            } catch (Exception $exception) {

                Mage::helper('M2ePro/Exception')->process($exception,true);

                $this->addListingsLogsMessage(
                    reset($listingProductsPack), Mage::helper('M2ePro')->__($exception->getMessage()),
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                continue;
            }

            foreach($response as $key => $value) {
                if ($value === false || empty($value['asin']) ) {
                    $result[] = $listingProductsPack[$key];
                } else {
                    $this->updateListingProduct($listingProductsPack[$key], $value['asin']);
                }
            }
        }

        return $result;
    }

    private function updateListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct, $generalId)
    {
        $tempSku = $listingProduct->getChildObject()->getAddingSku();

        $data = array(
            'general_id' => $generalId,
            'is_isbn_general_id' => Ess_M2ePro_Helper_Component_Amazon::isISBN($generalId),
            'sku' => $tempSku,
            'existance_check_status' => Ess_M2ePro_Model_Amazon_Listing_Product::EXISTANCE_CHECK_STATUS_FOUND,
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED
        );

        $listingProduct->addData($data)->save();

        $dataForAdd = array(
            'account_id' => $listingProduct->getListing()->getGeneralTemplate()->getAccountId(),
            'marketplace_id' => $listingProduct->getListing()->getGeneralTemplate()->getMarketplaceId(),
            'sku' => $tempSku,
            'product_id' => $listingProduct->getProductId(),
            'store_id' => $listingProduct->getListing()->getStoreId()
        );

        Mage::getModel('M2ePro/Amazon_Item')->setData($dataForAdd)->save();

        $message = Mage::helper('M2ePro')->__('The product was found in your Amazon inventory and linked by SKU.');

        $this->addListingsProductsLogsMessage(
            $listingProduct, $message,
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );
    }

    // ########################################
}