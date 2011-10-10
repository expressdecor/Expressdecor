<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_GiftRegistry
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Front end helper block to show giftregistry items
 */
class Enterprise_GiftRegistry_Block_Items extends Mage_Checkout_Block_Cart
{

    /**
     * Return list of gift registry items
     *
     * @return Enterprise_GiftRegistry_Model_Mysql4_Item_Collection
     */
    public function getItems()
    {
         if (!$this->hasItemCollection()) {
             $collection = Mage::getModel('enterprise_giftregistry/item')->getCollection()
                ->addRegistryFilter($this->getEntity()->getId());

            $quoteItemsCollection = array();
            $quote = Mage::getModel('sales/quote')->setItemCount(true);
            foreach ($collection as $item) {
                $product = $item->getProduct();
                $request = new Varien_Object(unserialize($item->getCustomOptions()));

                $candidate = $product->getTypeInstance(true)->prepareForCart($request, $product);

                if ($candidate && is_array($candidate)) {
                    $candidate = array_shift($candidate);
                    $options = $candidate->getCustomOptions();

                    $remainingQty = $item->getQty() - $item->getQtyFulfilled();
                    if ($remainingQty < 0) {
                        $remainingQty = 0;
                    }

                    $quoteItem = Mage::getModel('sales/quote_item')
                        ->addData($item->getData())
                        ->setQuote($quote)
                        ->setProduct($product)
                        ->setRemainingQty($remainingQty);

                    foreach ($options as $code => $option) {
                        $quoteItem->addOption($option);
                    }
                    $product->setCustomOptions($options);
                    $quoteItem->setGiftRegistryPrice($product->getFinalPrice());

                    $quoteItemsCollection[] = $quoteItem;
                }
            }

            $this->setData('item_collection', $quoteItemsCollection);
        }
        return $this->_getData('item_collection');
    }

    /**
     * Return current gift registry entity
     *
     * @return Enterprise_GiftRegistry_Model_Mysql4_Item_Collection
     */
    public function getEntity()
    {
         if (!$this->hasEntity()) {
            $this->setData('entity', Mage::registry('current_entity'));
        }
        return $this->_getData('entity');
    }

    /**
     * Return "add to cart" url
     *
     * @param Enterprise_GiftRegistry_Model_Item $item
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('*/*/addToCart', array('_current' => true));
    }

    /**
     * Return update action form url
     *
     * @return string
     */
    public function getActionUpdateUrl()
    {
        return $this->getUrl('*/*/updateItems', array('_current' => true));
    }

    /**
     * Return back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('giftregistry');
    }

}
