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
 * GiftRegistry entity item collection
 */
class Enterprise_GiftRegistry_Model_Mysql4_Item_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_init('enterprise_giftregistry/item', 'item_id');
    }

    /**
     * Add gift registry filter to collection
     *
     * @param int $entityId
     * @return Enterprise_GiftRegistry_Model_Mysql4_Item_Collection
     */
    public function addRegistryFilter($entityId)
    {
        $this->getSelect()
            ->join(array('e' => $this->getTable('enterprise_giftregistry/entity')),
                'e.entity_id = main_table.entity_id', 'website_id')
            ->where('main_table.entity_id = ?', $entityId);

        return $this;
    }

    /**
     * After load processing
     *
     * @return Enterprise_GiftRegistry_Model_Mysql4_Item_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->_assignProducts();

        return $this;
    }

    /**
     * Add products to items
     *
     * @return Enterprise_GiftRegistry_Model_Mysql4_Item_Collection
     */
    protected function _assignProducts()
    {
        $itemIds = array();
        $tempItems = $this->_items;
        $productIds = array();

        foreach ($tempItems as $offset => $item) {
            $productIds[] = $item->getproductId();
        }

        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addIdFilter($productIds)
            ->addAttributeToSelect(Mage::getSingleton('sales/quote_config')->getProductAttributes())
            ->addStoreFilter()
            ->addUrlRewrite()
            ->addOptionsToResult()
            ;

        foreach ($tempItems as $offset => $item) {
            $currentProduct = false;
            foreach ($productCollection as $product) {
                if ($product->getId() == $item->getProductId()) {
                    $currentProduct = $product;
                    break;
                }
            }

            if (!$currentProduct) {
                unset($this->_items[$offset]);
            } else {
                $item->setProduct(clone $currentProduct);//clone - prevent bundle collection single attribute attaching
                $item->setProductName($currentProduct->getName());
                $item->setProductSku($currentProduct->getSku());
                $item->setProductPrice($currentProduct->getPrice());
            }
        }
        return $this;
    }

    /**
     * Update items custom price (Depends on custom options)
     */
    public function updateItemAttributes()
    {
        foreach ($this->getItems() as $item) {
            $product = $item->getProduct();
            $request = new Varien_Object(unserialize($item->getCustomOptions()));
            $product->setSkipCheckRequiredOption(true);
            $product->getStore()->setWebsiteId($item->getWebsiteId());

            $candidate = $product->getTypeInstance(true)->prepareForCart($request, $product);
            if (is_array($candidate)) {
                $candidate = array_shift($candidate);
                $product->setCustomOptions($candidate->getCustomOptions());
                $item->setPrice($product->getFinalPrice());

                if ($simpleOption = $product->getCustomOption('simple_product')) {
                    $item->setSku($simpleOption->getProduct()->getSku());
                } else {
                    $item->setSku($product->getSku());
                }
            }
        }
    }
}
