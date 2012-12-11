<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Search_Dispatcher
{
    // ########################################

    public function runManual(Ess_M2ePro_Model_Listing_Product $listingProduct, $query,
                              Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                              Ess_M2ePro_Model_Account $account = NULL)
    {
        if (!$this->checkSearchConditions($listingProduct) || empty($query)) {
            return false;
        }

        $params = array(
            'listing_product' => $listingProduct,
            'query' => $query
        );

        if (is_null($marketplace)) {
            $marketplace = $listingProduct->getGeneralTemplate()->getMarketplace();
        }

        if (is_null($account)) {
            $account = $listingProduct->getGeneralTemplate()->getAccount();
        }

        try {
            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector')->getDispatcher();
            $dispatcherObject->processConnector('search', 'manual' ,'requester',
                                                $params, $marketplace, $account,
                                                'Ess_M2ePro_Model_Amazon');
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Exception')->process($exception,true);
            return false;
        }

        $result = Mage::helper('M2ePro')->getGlobalValue('temp_amazon_manual_search_asin_result');
        Mage::helper('M2ePro')->unsetGlobalValue('temp_amazon_manual_search_asin_result');

        if (!is_array($result)) {
            return array();
        }

        return $result;
    }

    // ########################################

    public function runAutomatic(array $listingsProducts)
    {
        $listingsProductsFiltered = array();

        foreach ($listingsProducts as $listingProduct) {

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                continue;
            }

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$this->checkSearchConditions($listingProduct)) {
                continue;
            }

            $listingsProductsFiltered[] = $listingProduct;
        }

        if (count($listingsProductsFiltered) <= 0) {
            return false;
        }

        $listingsProductsByAsin = array();
        $listingsProductsByQuery = array();
        $listingsProductsFailed = array();

        foreach ($listingsProductsFiltered as $listingProductFiltered) {

            /** @var $listingProductFiltered Ess_M2ePro_Model_Listing_Product */
            $tempGeneralId = $listingProductFiltered->getChildObject()->getAddingGeneralId();

            if (empty($tempGeneralId)) {
                continue;
            }

            $isAsin = Mage::helper('M2ePro/Component_Amazon')->isASIN($tempGeneralId);

            if (!$isAsin) {

                $isIsbn = Mage::helper('M2ePro/Component_Amazon')->isISBN($tempGeneralId);

                if (!$isIsbn) {

                    $childListingProductFiltered = $listingProductFiltered->getChildObject();

                    $temp = Ess_M2ePro_Model_Amazon_Listing_Product::GENERAL_ID_SEARCH_STATUS_NONE;
                    $childListingProductFiltered->setData('general_id_search_status',
                                                          $temp);
                    $message = Mage::helper('M2ePro')
                                    ->__('ASIN/ISBN has a wrong format. Please, check its value in product settings.');
                    $childListingProductFiltered->setData('general_id_search_suggest_data',
                                                          json_encode(array('message'=>$message)));
                    $childListingProductFiltered->save();

                    $listingsProductsFailed[] = $listingProductFiltered;

                    continue;
                }
            }

            $listingsProductsByAsin[] = $listingProductFiltered;
        }

        $listingsProductsByAsinIds = array();
        foreach ($listingsProductsByAsin as $listingProductByAsin) {
            /** @var $listingProductByAsin Ess_M2ePro_Model_Listing_Product */
            $listingsProductsByAsinIds[] = $listingProductByAsin->getId();
        }

        $listingsProductsFailedIds = array();
        foreach ($listingsProductsFailed as $listingProductFailed) {
            /** @var $listingProductFailed Ess_M2ePro_Model_Listing_Product */
            $listingsProductsFailedIds[] = $listingProductFailed->getId();
        }

        foreach ($listingsProductsFiltered as $listingProductFiltered) {
            /** @var $listingProductFiltered Ess_M2ePro_Model_Listing_Product */
            if (in_array($listingProductFiltered->getId(),$listingsProductsFailedIds) ||
                in_array($listingProductFiltered->getId(),$listingsProductsByAsinIds)) {
                continue;
            }
            $listingsProductsByQuery[] = $listingProductFiltered;
        }

        try {
            $this->runAutomaticByAsin($listingsProductsByAsin);
            $this->runAutomaticByQuery($listingsProductsByQuery);
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Exception')->process($exception,true);
            return false;
        }

        return true;
    }

    //----------------------------------------

    private function runAutomaticByAsin(array $listingsProducts)
    {
        $accountsMarketplaces = array();

        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            /** @var $account Ess_M2ePro_Model_Account */
            $account = $listingProduct->getGeneralTemplate()->getAccount();
            /** @var $marketplace Ess_M2ePro_Model_Marketplace */
            $marketplace = $listingProduct->getGeneralTemplate()->getMarketplace();

            $identifier = $account->getId().'_'.$marketplace->getId();

            if (!isset($accountsMarketplaces[$identifier])) {
                $accountsMarketplaces[$identifier] = array(
                    'account' => $account,
                    'marketplace' => $marketplace,
                    'listings_products' => array()
                );
            }

            $accountsMarketplaces[$identifier]['listings_products'][] = $listingProduct;
        }

        foreach ($accountsMarketplaces as $accountMarketplace) {

            /** @var $account Ess_M2ePro_Model_Account */
            $account = $accountMarketplace['account'];
            /** @var $marketplace Ess_M2ePro_Model_Marketplace */
            $marketplace = $accountMarketplace['marketplace'];

            $listingsProductsParts = array_chunk($accountMarketplace['listings_products'],10);

            foreach ($listingsProductsParts as $listingsProductsPart) {

                if (count($listingsProductsPart) <= 0) {
                    continue;
                }

                $params = array(
                    'listings_products' => $listingsProductsPart
                );

                $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector')->getDispatcher();
                $dispatcherObject->processConnector('automatic', 'byAsin' ,'requester',
                                                    $params, $marketplace, $account,
                                                    'Ess_M2ePro_Model_Amazon_Search');
            }
        }
    }

    private function runAutomaticByQuery(array $listingsProducts)
    {
        foreach ($listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $params = array(
                'listing_product' => $listingProduct
            );

            $marketplace = $listingProduct->getGeneralTemplate()->getMarketplace();
            $account = $listingProduct->getGeneralTemplate()->getAccount();

            $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector')->getDispatcher();
            $dispatcherObject->processConnector('automatic', 'byQuery' ,'requester',
                                                $params, $marketplace, $account,
                                                'Ess_M2ePro_Model_Amazon_Search');
        }
    }

    // ########################################

    private function checkSearchConditions(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        return $listingProduct->isNotListed() &&
               !$listingProduct->getChildObject()->getCategoryId() &&
               !$listingProduct->getChildObject()->getGeneralId();
    }

    // ########################################
}