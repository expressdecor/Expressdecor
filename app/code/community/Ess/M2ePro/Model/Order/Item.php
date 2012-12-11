<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Order_Item extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    // ->__('Product does not exist. Probably it was deleted.');
    // ->__('Product is disabled.');
    // ->__('Order Import does not support product type: %type%.');

    // ########################################

    /** @var $order Ess_M2ePro_Model_Order */
    private $order = NULL;

    /** @var $product Mage_Catalog_Model_Product */
    private $product = NULL;

    private $proxy = NULL;

    private $supportedProductTypes = array(
        Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
        Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
        Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
        Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
    );

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Item');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Order_Item|Ess_M2ePro_Model_Amazon_Order_Item
     */
    public function getChildObject()
    {
        return parent::getChildObject();
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return $this->getChildObject()->isLocked();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->order = NULL;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    // ########################################

    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    public function getProductId()
    {
        return $this->getData('product_id');
    }

    // ########################################

    public function setOrder(Ess_M2ePro_Model_Order $order)
    {
        $this->order = $order;
        return $this;
    }

    public function getOrder()
    {
        if (is_null($this->order)) {
            $this->order = Mage::helper('M2ePro/Component')
                ->getComponentObject($this->getComponentMode(), 'Order', $this->getOrderId());
        }

        return $this->order;
    }

    // ########################################

    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    public function getProduct()
    {
        if (is_null($this->getProductId())) {
            return NULL;
        }

        if (is_null($this->product)) {
            $storeId = $this->getChildObject()->getAssociatedStoreId();

            if ($storeId == 0) {
                $storeId = Mage::helper('M2ePro/Magento')->getDefaultStoreId();
            }

            $this->product = Mage::getModel('catalog/product')
                ->setStoreId($storeId)
                ->load($this->getProductId());
        }

        return $this->product;
    }

    // ########################################

    public function getProxy()
    {
        if (is_null($this->proxy)) {
            $this->proxy = $this->getChildObject()->getProxy();
        }

        return $this->proxy;
    }

    // ########################################

    public function associateWithProduct()
    {
        $productId = !is_null($this->getProductId())
            ? $this->getProductId()
            : $this->getChildObject()->getAssociatedProductId();

        if (is_null($this->getProductId())) {
            $this->setData('product_id', $productId)->save();
        }

        if (is_null($this->getProduct()->getId())) {
            $this->setData('product_id', NULL)->save();

            throw new Exception('Product does not exist. Probably it was deleted.');
        }

        if ($this->getProduct()->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            throw new Exception('Product is disabled.');
        }

        if (!in_array($this->getProduct()->getTypeId(), $this->supportedProductTypes)) {
            $message = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription(
                'Order Import does not support product type: %type%.', array('type' => $this->getProduct()->getTypeId())
            );

            throw new Exception($message);
        }
    }

    // ########################################
}