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
 * Entity items data model
 */
class Enterprise_GiftRegistry_Model_Item extends Mage_Core_Model_Abstract
{
    function _construct() {
        $this->_init('enterprise_giftregistry/item');
    }

    /**
     * Load item by registry id and product id
     *
     * @param int $registryId
     * @param int $productId
     * @return Enterprise_GiftRegistry_Model_Item
     */
    public function loadByProductRegistry($registryId, $productId)
    {
        $this->_getResource()->loadByProductRegistry($this, $registryId, $productId);
        return $this;
    }

    /**
     * Add or Move item product to shopping cart
     *
     * Return true if product was successful added or exception with code
     * Return false for disabled or unvisible products
     *
     * @throws Mage_Core_Exception
     * @param Mage_Checkout_Model_Cart $cart
     * @param int $qty
     * @return bool
     */
    public function addToCart(Mage_Checkout_Model_Cart $cart, $qty)
    {
        $product = $this->_getProduct();
        $storeId = $this->getStoreId();

        if ($this->getQty() < ($qty + $this->getQtyFulfilled())) {
            $qty = $this->getQty() - $this->getQtyFulfilled();
        }

        if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return false;
        }

        if (!$product->isVisibleInSiteVisibility()) {
            if ($product->getStoreId() == $storeId) {
                return false;
            }
            $urlData = Mage::getResourceSingleton('catalog/url')
                ->getRewriteByProductStore(array($product->getId() => $storeId));
            if (!isset($urlData[$product->getId()])) {
                return false;
            }
            $product->setUrlDataObject(new Varien_Object($urlData));
            $visibility = $product->getUrlDataObject()->getVisibility();
            if (!in_array($visibility, $product->getVisibleInSiteVisibilities())) {
                return false;
            }
        }

        if (!$product->isSalable()) {
            Mage::throwException(
                Mage::helper('enterprise_giftregistry')->__('This product(s) is currently out of stock.'));
        }

        $product->setGiftregistryItemId($this->getId());
        $product->addCustomOption('giftregistry_id', $this->getEntityId());
        $request = unserialize($this->getCustomOptions());
        $request['qty'] = $qty;

        $cart->addProduct($product, $request);
        if (!empty($request['related_product'])) {
            $cart->addProductsByIds(explode(',', $request['related_product']));
        }

        if (!$product->isVisibleInSiteVisibility()) {
            $cart->getQuote()->getItemByProduct($product)->setStoreId($storeId);
        }
    }

    /**
     * Check product representation in item
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  bool
     */
    public function isRepresentProduct($product)
    {
        if ($this->getProductId() != $product->getId()) {
            return false;
        }

        $productCustomOptions = $product->getCustomOptions();

        if (empty($productCustomOptions['info_buyRequest'])) {
            return false;
        }
        $requestOption = $productCustomOptions['info_buyRequest'];
        $requestArray = unserialize($requestOption->getValue());
        $selfOptions = unserialize($this->getCustomOptions());

        if(!$this->_compareOptions($requestArray, $selfOptions)){
            return false;
        }
        if(!$this->_compareOptions($selfOptions, $requestArray)){
            return false;
        }
        return true;
    }

    /**
     * Check if two options array are identical
     *
     * @param array $options1
     * @param array $options2
     * @return bool
     */
    protected function _compareOptions($options1, $options2)
    {
        $skipOptions = array('qty');
        foreach ($options1 as $code => $value) {
            if (in_array($code, $skipOptions)) {
                continue;
            }
            if (!isset($options2[$code]) || $options2[$code] != $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Set product attributes to item
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Enterprise_GiftRegistry_Model_Item
     */
    public function setProduct($product)
    {
        $this->setName($product->getName());
        $this->setData('product', $product);
        return $this;
    }

    /**
     * Return product url
     *
     * @return bool
     */
    public function getProductUrl()
    {
        return $this->getProduct()->getProductUrl();
    }


    /**
     * Return item product
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        if (!$this->getProduct()) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            if (!$product->getId()) {
                Mage::throwException(
                    Mage::helper('enterprise_giftregistry')->__('Invalid product for adding item to quote.'));
            }
            $this->setProduct($product);
        }
        return $this->getProduct();
    }
}
