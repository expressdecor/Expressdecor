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
 * @package     Enterprise_Checkout
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Admin Checkout processing model
 *
 * @category   Enterprise
 * @package    Enterprise_Checkout
 */
class Enterprise_Checkout_Model_Cart extends Varien_Object
{
    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote;

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;


    /**
     * Setter for $_customer
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Enterprise_Checkout_Model_Cart
     */
    public function setCustomer($customer)
    {
        if ($customer instanceof Varien_Object && $customer->getId()) {
            $this->_customer = $customer;
            $this->_quote = null;
        }
        return $this;
    }

    /**
     * Getter for $_customer
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * Return quote store
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->getQuote()->getStore();
    }

    /**
     * Return current active quote for specified customer
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (!is_null($this->_quote)) {
            return $this->_quote;
        }

        $this->_quote = Mage::getModel('sales/quote');

        if ($this->getCustomer() !== null) {
            $this->_quote
                ->setSharedStoreIds($this->getQuoteSharedStoreIds())
                ->loadByCustomer($this->getCustomer()->getId());
        }

        return $this->_quote;
    }

    /**
     * Return appropriate store ids for retrieving quote in current store
     * Correct customer shared store ids when customer has Admin Store
     *
     * @return array
     */
    public function getQuoteSharedStoreIds()
    {
        if ($this->getStoreId()) {
            return Mage::app()->getStore($this->getStoreId())
                ->getWebsite()
                ->getStoreIds();
        }
        if (!$this->getCustomer()) {
            return array();
        }
        if ((bool)$this->getCustomer()->getSharingConfig()->isWebsiteScope()) {
            return Mage::app()->getWebsite($this->getCustomer()->getWebsiteId())->getStoreIds();
        } else {
            return $this->getCustomer()->getSharedStoreIds();
        }
    }

    /**
     * Create quote by demand or return active customer quote if it exists
     *
     * @return Mage_Sales_Model_Quote
     */
    public function createQuote()
    {
        if (!$this->getQuote()->getId() && $this->getCustomer() !== null) {
            $this->getQuote()
                ->assignCustomer($this->getCustomer())
                ->save();
        }
        return $this->getQuote();
    }

    /**
     * Recollect quote and save it
     *
     * @param bool $recollect Collect quote totals or not
     * @return Enterprise_Checkout_Model_Cart
     */
    public function saveQuote($recollect = true)
    {
        if (!$this->getQuote()->getId()) {
            return $this;
        }
        if ($recollect) {
            $this->getQuote()->collectTotals();
        }
        $this->getQuote()->save();
        return $this;
    }

    /**
     * Return preferred non-admin store Id
     * If Customer has active quote - return its store, otherwise try to get customer store or default store
     *
     * @return int|bool
     */
    public function getPreferredStoreId()
    {
        $storeId = false;
        $quote = $this->getQuote();
        $customer = $this->getCustomer();

        if ($quote->getId() && $quote->getStoreId()) {
            $storeId = $quote->getStoreId();
        } elseif ($customer !== null && $customer->getStoreId() && !$customer->getStore()->isAdmin()) {
            $storeId = $customer->getStoreId();
        } else {
            $customerStoreIds = $this->getQuoteSharedStoreIds(); //$customer->getSharedStoreIds();
            $storeId = array_shift($customerStoreIds);
            if (Mage::app()->getStore($storeId)->isAdmin()) {
                $defaultStore = Mage::app()->getAnyStoreView();
                if ($defaultStore) {
                    $storeId = $defaultStore->getId();
                }
            }
        }

        return $storeId;
    }

    /**
     * Add product to current quote
     *
     * @param mixed $product
     * @param mixed $qty
     * @return Mage_Sales_Model_Quote_Item
     * @throws Mage_Core_Exception
     */
    public function addProduct($product, $qty=1)
    {
        $qty = (float)$qty;
        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $productId = $product;
            $product = Mage::getModel('catalog/product')
                ->setStore($this->getStore())
                ->setStoreId($this->getStore()->getId())
                ->load($product);
            if (!$product->getId()) {
                Mage::throwException(Mage::helper('enterprise_checkout')->__('Failed to add a product to cart by id "%s"', $productId));
            }
        }

        if($product->getStockItem()) {
            $product->getStockItem()->setCustomerGroupId($this->getCustomer()->getGroupId());
            if (!$product->getStockItem()->getIsQtyDecimal()) {
                $qty = (int)$qty;
            }
            else {
                $product->setIsQtyDecimal(1);
            }
        }
        $qty = $qty > 0 ? $qty : 1;
        if ($item = $this->createQuote()->getItemByProduct($product)) {
            $item->setQty($item->getQty()+$qty);
        }
        else {
            $product->setSkipCheckRequiredOption(true);
            $item = $this->createQuote()->addProduct($product, $qty);
            if (is_string($item)) {
                Mage::throwException($item);
            }
            $product->unsSkipCheckRequiredOption();
            $item->checkData();
        }
        return $item;
    }

    /**
     * Add new item to quote based on existing order Item
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return Mage_Sales_Model_Quote_Item
     * @throws Mage_Core_Exception
     */
    public function reorderItem(Mage_Sales_Model_Order_Item $orderItem, $qty = 1)
    {
        if (!$orderItem->getId()) {
            Mage::throwException(Mage::helper('enterprise_checkout')->__('Failed to reorder item'));
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getStore()->getId())
            ->load($orderItem->getProductId());

        if ($product->getId()) {
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new Varien_Object($info);
            $product->setSkipCheckRequiredOption(true);
            $item = $this->createQuote()->addProduct($product, $info);
            if (is_string($item)) {
                Mage::throwException($item);
            }

            $item->setQty($qty);

            if ($additionalOptions = $orderItem->getProductOptionByCode('additional_options')) {
                $item->addOption(new Varien_Object(
                    array(
                        'product' => $item->getProduct(),
                        'code' => 'additional_options',
                        'value' => serialize($additionalOptions)
                    )
                ));
            }

            Mage::dispatchEvent('sales_convert_order_item_to_quote_item', array(
                'order_item' => $orderItem,
                'quote_item' => $item
            ));

            return $item;

        } else {
            Mage::throwException(Mage::helper('enterprise_checkout')->__('Failed to add a product of order item'));
        }
    }

    /**
     * Remove items from quote or move them to wishlist etc.
     *
     * @param array $data Array of items
     * @return Enterprise_Checkout_Model_Cart
     */
    public function updateQuoteItems($data)
    {
        if (!$this->getQuote()->getId()) {
            return $this;
        }
        if (is_array($data)) {
            foreach ($data as $itemId => $info) {
                $item = $this->getQuote()->getItemById($itemId);
                $itemQty = (float)$info['qty'];
                if ($item && $item->getProduct()->getStockItem()) {
                    $item->getProduct()->getStockItem()->setCustomerGroupId($this->getCustomer()->getGroupId());
                    if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                        $itemQty = (int)$info['qty'];
                    }
                    else {
                        $item->setIsQtyDecimal(1);
                    }
                }

                // remove items with zero and negative quantity
                if ($itemQty < 0.00001) {
                    $this->moveQuoteItem($itemId, false);
                    continue;
                }

                if (empty($info['action'])) {
                    if ($item) {
                        $item->setQty($itemQty);
                        $item->getProduct()->setIsSuperMode(true);
                        $item->checkData();
                    }
                }
                else {
                    $this->moveQuoteItem($itemId, $info['action']);
                }
            }
        }
        return $this;
    }

    /**
     * Move quote item to wishlist
     *
     * @param Mage_Sales_Model_Quote_Item|int $item
     * @param string $moveTo Destination storage
     * @return Enterprise_Checkout_Model_Cart
     */
    public function moveQuoteItem($item, $moveTo)
    {
        if ($item = $this->_getQuoteItem($item)) {
            switch ($moveTo) {
                case 'wishlist':
                    $wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($this->getCustomer(), true)
                        ->setStore($this->getStore())
                        ->setSharedStoreIds($this->getStore()->getWebsite()->getStoreIds());
                    if ($wishlist->getId()) {
                        $wishlistItem = $wishlist->addNewItem($item->getProduct()->getId());
                        if ($wishlistItem->getId()) {
                            $this->getQuote()->removeItem($item->getId());
                        }
                    }
                    break;
                default:
                    $this->getQuote()->removeItem($item->getId());
                    break;
            }
        }
        return $this;
    }

    /**
     * Create duplicate of quote preserving all data (items, addresses, payment etc.)
     *
     * @param Mage_Sales_Model_Quote $quote Original Quote
     * @param bool $active Create active quote or not
     * @return Mage_Sales_Model_Quote New created quote
     */
    public function copyQuote(Mage_Sales_Model_Quote $quote, $active = false)
    {
        if (!$quote->getId()) {
            return $quote;
        }
        $newQuote = clone $quote;
        $newQuote->setId(null);
        $newQuote->setIsActive($active ? 1 : 0);
        $newQuote->save();

        // copy items with their options
        $newParentItemIds = array();
        foreach ($quote->getItemsCollection() as $item) {
            // save child items later
            if ($item->getParentItem()) {
                continue;
            }
            $oldItemId = $item->getId();
            $newItem = clone $item;
            $newItem->setQuote($newQuote);
            $newItem->save();
            $newParentItemIds[$oldItemId] = $newItem->getId();
        }

        // save childs with new parent id
        foreach ($quote->getItemsCollection() as $item) {
            if (!$item->getParentItem() || !isset($newParentItemIds[$item->getParentItemId()])) {
                continue;
            }
            $newItem = clone $item;
            $newItem->setQuote($newQuote);
            $newItem->setParentItemId($newParentItemIds[$item->getParentItemId()]);
            $newItem->save();
        }

        // copy billing and shipping addresses
        foreach ($quote->getAddressesCollection() as $address) {
            $address->setQuote($newQuote);
            $address->setId(null);
            $address->save();
        }

        // copy payment info
        foreach ($quote->getPaymentsCollection() as $payment) {
            $payment->setQuote($newQuote);
            $payment->setId(null);
            $payment->save();
        }

        return $newQuote;
    }

    /**
     * Wrapper for getting quote item
     *
     * @param Mage_Sales_Model_Quote_Item|int $item
     * @return Mage_Sales_Model_Quote_Item|bool
     */
    protected function _getQuoteItem($item)
    {
        if ($item instanceof Mage_Sales_Model_Quote_Item) {
            return $item;
        }
        elseif (is_numeric($item)) {
            return $this->getQuote()->getItemById($item);
        }
        return false;
    }
}
