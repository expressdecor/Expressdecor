<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_StockItem
{
    //####################################

    public function catalogInventoryStockItemSaveAfter(Varien_Event_Observer $observer)
    {
        try {

             // Get product id
             $productId = $observer->getData('item')->getData('product_id');

             // Get listings, other listings where is product
             $listingsArray = Mage::getResourceModel('M2ePro/Listing')->getListingsWhereIsProduct($productId);
             $otherListingsArray = Mage::getResourceModel('M2ePro/Listing_Other')->getItemsWhereIsProduct($productId);

             if (count($listingsArray) > 0 || count($otherListingsArray) > 0) {

                    // Save global changes
                    //--------------------
                    Mage::getModel('M2ePro/ProductChange')
                                ->updateAttribute( $productId,
                                                   'product_instance',
                                                   'any_old',
                                                   'any_new',
                                                    Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER );
                    //--------------------

                    // Save changes for qty
                    //--------------------
                    $qtyOld = (int)$observer->getData('item')->getOrigData('qty');
                    $qtyNew = (int)$observer->getData('item')->getData('qty');

                    $rez = Mage::getModel('M2ePro/ProductChange')
                                 ->updateAttribute( $productId,
                                                    'qty',
                                                    $qtyOld,
                                                    $qtyNew,
                                                    Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER );

                    if ($rez !== false) {

                          foreach ($listingsArray as $listingTemp) {

                                 $tempLog = Mage::getModel('M2ePro/Listing_Log');
                                 $tempLog->setComponentMode($listingTemp['component_mode']);
                                 $tempLog->addProductMessage(
                                    $listingTemp['id'],
                                    $productId,
                                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                                    NULL,
                                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_QTY,
                                    // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                                    Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                                        'From [%from%] to [%to%]',array('!from'=>$qtyOld,'!to'=>$qtyNew)
                                    ),
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                                 );
                          }

                          foreach ($otherListingsArray as $otherListingTemp) {

                                 $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
                                 $tempLog->setComponentMode($otherListingTemp['component_mode']);
                                 $tempLog->addProductMessage(
                                    $otherListingTemp['id'],
                                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                                    NULL,
                                    Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_QTY,
                                    // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                                    Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                                        'From [%from%] to [%to%]',array('!from'=>$qtyOld,'!to'=>$qtyNew)
                                    ),
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                                 );
                          }
                    }
                    //--------------------

                    // Save changes for stock Availability
                    //--------------------
                    $stockAvailabilityOld = (bool)$observer->getData('item')->getOrigData('is_in_stock');
                    $stockAvailabilityNew = (bool)$observer->getData('item')->getData('is_in_stock');

                    $rez = Mage::getModel('M2ePro/ProductChange')
                                     ->updateAttribute( $productId,
                                                        'stock_availability',
                                                        (int)$stockAvailabilityOld,
                                                        (int)$stockAvailabilityNew,
                                                        Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER );

                    if ($rez !== false) {

                          $stockAvailabilityOld = $stockAvailabilityOld ? 'IN Stock' : 'OUT of Stock';
                          $stockAvailabilityNew = $stockAvailabilityNew ? 'IN Stock' : 'OUT of Stock';

                          foreach ($listingsArray as $listingTemp) {

                                 $tempLog = Mage::getModel('M2ePro/Listing_Log');
                                 $tempLog->setComponentMode($listingTemp['component_mode']);
                                 $tempLog->addProductMessage(
                                    $listingTemp['id'],
                                    $productId,
                                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                                    NULL,
                                    Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                                    // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                                    // Parser hack -> Mage::helper('M2ePro')->__('IN Stock');
                                    // Parser hack -> Mage::helper('M2ePro')->__('OUT of Stock');
                                    Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                                        'From [%from%] to [%to%]',
                                        array('from'=>$stockAvailabilityOld,'to'=>$stockAvailabilityNew)
                                    ),
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                                 );
                          }

                        foreach ($otherListingsArray as $otherListingTemp) {

                                 $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
                                 $tempLog->setComponentMode($otherListingTemp['component_mode']);
                                 $tempLog->addProductMessage(
                                    $otherListingTemp['id'],
                                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                                    NULL,
                                    Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_STOCK_AVAILABILITY,
                                    // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                                    // Parser hack -> Mage::helper('M2ePro')->__('IN Stock');
                                    // Parser hack -> Mage::helper('M2ePro')->__('OUT of Stock');
                                    Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                                        'From [%from%] to [%to%]',
                                        array('from'=>$stockAvailabilityOld,'to'=>$stockAvailabilityNew)
                                    ),
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                                 );
                          }
                    }
                    //--------------------
            }

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Exception')->process($exception,true);
            return;
        }
    }

    //####################################
}