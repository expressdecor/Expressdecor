<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Order extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    // ->__('Magento Order was not created. Reason: %msg%');
    // ->__('Magento Order #%order_id% was created.');
    // ->__('Payment Transaction was not created. Reason: %msg%');
    // ->__('Invoice was not created. Reason: %msg%');
    // ->__('Invoice #%invoice_id% was created.');
    // ->__('Shipment was not created. Reason: %msg%');
    // ->__('Shipment #%shipment_id% was created.');
    // ->__('Tracking details were not imported. Reason: %msg%');
    // ->__('Tracking details were imported.');
    // ->__('Magento Order #%order_id% was canceled.');
    // ->__('Magento Order #%order_id% was not canceled. Reason: %msg%');
    // ->__('Store does not exist.');
    // ->__('Payment method "M2E Pro Payment" is disabled in magento configuration.')
    // ->__('Shipping method "M2E Pro Shipping" is disabled in magento configuration.')

    // ########################################

    private $account = NULL;

    private $marketplace = NULL;

    private $magentoOrder = NULL;

    private $shippingAddress = NULL;

    private $itemsCollection = NULL;

    private $proxy = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Order|Ess_M2ePro_Model_Amazon_Order
     */
    public function getChildObject()
    {
        return parent::getChildObject();
    }

    // ########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        foreach ($this->getItemsCollection()->getItems() as $item) {
            /** @var $item Ess_M2ePro_Model_Order_Item */
            $item->deleteInstance();
        }

        $this->deleteChildInstance();

        $this->account = NULL;
        $this->magentoOrder = NULL;
        $this->itemsCollection = NULL;
        $this->proxy = NULL;

        $this->delete();

        return true;
    }

    // ########################################

    public function getAccountId()
    {
        return $this->getData('account_id');
    }

    public function getMarketplaceId()
    {
        return $this->getData('marketplace_id');
    }

    public function getMagentoOrderId()
    {
        return $this->getData('magento_order_id');
    }

    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    // ########################################

    public function setAccount(Ess_M2ePro_Model_Account $account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->account)) {
            $this->account = Mage::helper('M2ePro/Component')
                ->getComponentObject($this->getComponentMode(), 'Account', $this->getAccountId());
        }

        return $this->account;
    }

    // ########################################

    public function setMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $this->marketplace = $marketplace;
        return $this;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplace)) {
            $this->marketplace = Mage::helper('M2ePro/Component')
                ->getComponentObject($this->getComponentMode(), 'Marketplace', $this->getMarketplaceId());
        }

        return $this->marketplace;
    }

    // ########################################

    /**
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    public function getItemsCollection()
    {
        if (is_null($this->itemsCollection)) {
            $this->itemsCollection = Mage::helper('M2ePro/Component')
                ->getComponentCollection($this->getComponentMode(), 'Order_Item')
                ->addFieldToFilter('order_id', $this->getId());

            foreach ($this->itemsCollection as $item) {
                /** @var $item Ess_M2ePro_Model_Order_Item */
                $item->setOrder($this);
            }
        }

        return $this->itemsCollection;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isSingle()
    {
        return $this->getItemsCollection()->count() == 1;
    }

    /**
     * @return bool
     */
    public function isCombined()
    {
        return $this->getItemsCollection()->count() > 1;
    }

    // ---------------------------------------

    public function hasListingItems()
    {
        $relatedChannelItems = $this->getChildObject()->getRelatedChannelItems();

        return count($relatedChannelItems) > 0;
    }

    public function hasOtherListingItems()
    {
        $relatedChannelItems = $this->getChildObject()->getRelatedChannelItems();

        return count($relatedChannelItems) != $this->getItemsCollection()->count();
    }

    // ########################################

    public function addLog($message, $type, array $params = array())
    {
        if (!empty($params)) {
            $message = Mage::getSingleton('M2ePro/Log_Abstract')->encodeDescription($message, $params);
        }

        Mage::getModel('M2ePro/Order_Log')->add($this->getComponentMode(), $this->getId(), $message, $type);
    }

    public function addSuccessLog($message, array $params = array())
    {
        $this->addLog($message, Ess_M2ePro_Model_Order_Log::TYPE_SUCCESS, $params);
    }

    public function addNoticeLog($message, array $params = array())
    {
        $this->addLog($message, Ess_M2ePro_Model_Order_Log::TYPE_NOTICE, $params);
    }

    public function addWarningLog($message, array $params = array())
    {
        $this->addLog($message, Ess_M2ePro_Model_Order_Log::TYPE_WARNING, $params);
    }

    public function addErrorLog($message, array $params = array())
    {
        $this->addLog($message, Ess_M2ePro_Model_Order_Log::TYPE_ERROR, $params);
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Order_ShippingAddress
     */
    public function getShippingAddress()
    {
        if (is_null($this->shippingAddress)) {
            $this->shippingAddress = $this->getChildObject()->getShippingAddress();
            $this->shippingAddress->setOrder($this);
        }

        return $this->shippingAddress;
    }

    // ########################################

    public function setMagentoOrder($order)
    {
        $this->magentoOrder = $order;
        return $this;
    }

    /**
     * @return null|Mage_Sales_Model_Order
     */
    public function getMagentoOrder()
    {
        if (is_null($this->getMagentoOrderId())) {
            return NULL;
        }

        if (is_null($this->magentoOrder)) {
            $this->magentoOrder = Mage::getModel('sales/order')->load($this->getMagentoOrderId());
        }

        return !is_null($this->magentoOrder->getId()) ? $this->magentoOrder : NULL;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Order_Proxy
     */
    public function getProxy()
    {
        if (is_null($this->proxy)) {
            $this->proxy = $this->getChildObject()->getProxy();
        }

        return $this->proxy;
    }

    // ########################################

    private function associateWithStore()
    {
        $storeId = $this->getStoreId() ? $this->getStoreId() : $this->getChildObject()->getAssociatedStoreId();

        if (is_null($this->getStoreId()) && !is_null($storeId)) {
            $this->setData('store_id', $storeId)->save();
        }

        // Load needed to have original store config
        $store = Mage::getModel('core/store')->load($storeId);

        if (is_null($store->getId())) {
            throw new Exception('Store does not exist.');
        }

        if (!Mage::getStoreConfig('payment/m2epropayment/active', $store)) {
            throw new Exception('Payment method "M2E Pro Payment" is disabled in magento configuration.');
        }

        if (!Mage::getStoreConfig('carriers/m2eproshipping/active', $store)) {
            throw new Exception('Shipping method "M2E Pro Shipping" is disabled in magento configuration.');
        }

        $this->getProxy()->setStore($store);
    }

    // ########################################

    private function associateItemsWithProducts()
    {
        foreach ($this->getItemsCollection()->getItems() as $item) {
            /** @var $item Ess_M2ePro_Model_Order_Item */
            $item->associateWithProduct();
        }
    }

    // ########################################

    public function canCreateMagentoOrder()
    {
        if (!is_null($this->getMagentoOrderId())) {
            return false;
        }

        if (!$this->getChildObject()->canCreateMagentoOrder()) {
            return false;
        }

        return true;
    }

    // ########################################

    public function createMagentoOrder()
    {
        try {
			//Alex
			if ($this->getChildObject()->beforeCreateMagentoOrder()) {
				
			
			//Alex
            //$this->getChildObject()->beforeCreateMagentoOrder();

            // Store must be initialized before products
            // ---------------
            $this->associateWithStore();
            $this->associateItemsWithProducts();
            // ---------------

            // Create magento order
            // ---------------
            /** @var $magentoQuoteBuilder Ess_M2ePro_Model_Magento_Quote */
            $magentoQuoteBuilder = Mage::getModel('M2ePro/Magento_Quote');
            $magentoQuoteBuilder->setProxyOrder($this->getProxy());
            $magentoQuoteBuilder->buildQuote();

            /** @var $magentoOrderBuilder Ess_M2ePro_Model_Magento_Order */
            $magentoOrderBuilder = Mage::getModel('M2ePro/Magento_Order');
            $magentoOrderBuilder->setQuote($magentoQuoteBuilder->getQuote());
            $magentoOrderBuilder->buildOrder();
            $magentoOrderBuilder->addComments($this->getProxy()->getComments());

            $this->magentoOrder = $magentoOrderBuilder->getOrder();
            $this->setData('magento_order_id', $this->magentoOrder->getId())->save();

            unset($magentoQuoteBuilder);
            unset($magentoOrderBuilder);
            // ---------------

            $this->updateMagentoOrderStatus();

            $this->getChildObject()->afterCreateMagentoOrder();

            $this->addSuccessLog('Magento Order #%order_id% was created.', array(
                '!order_id' => $this->getMagentoOrder()->getRealOrderId()
            ));
		//Alex
			} else {
				throw new Exception('Old order!');
			}
		//Alex
        } catch (Exception $e) {
            $this->addErrorLog('Magento Order was not created. Reason: %msg%', array('msg' => $e->getMessage()));
            throw $e;
        }

        return $this->magentoOrder;
    }

    private function updateMagentoOrderStatus()
    {
        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($this->getMagentoOrder());
        $magentoOrderUpdater->updateStatus($this->getChildObject()->getStatusForMagentoOrder());
        $magentoOrderUpdater->finishUpdate();
    }

    // ########################################

    public function canCancelMagentoOrder()
    {
        $magentoOrder = $this->getMagentoOrder();

        if (is_null($magentoOrder) || $magentoOrder->isCanceled()) {
            return false;
        }

        return true;
    }

    public function cancelMagentoOrder()
    {
        if (!$this->canCancelMagentoOrder()) {
            return;
        }

        try {
            /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
            $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
            $magentoOrderUpdater->setMagentoOrder($this->getMagentoOrder());
            $magentoOrderUpdater->cancel();

            $this->addSuccessLog('Magento Order #%order_id% was canceled.', array(
                '!order_id' => $this->getMagentoOrder()->getRealOrderId()
            ));
        } catch (Exception $e) {
            $this->addErrorLog('Magento Order #%order_id% was not canceled. Reason: %msg%', array(
                '!order_id' => $this->getMagentoOrder()->getRealOrderId(),
                'msg' => $e->getMessage()
            ));
            throw $e;
        }
    }

    // ########################################

    public function createInvoice()
    {
        $invoice = null;

        try {
            $invoice = $this->getChildObject()->createInvoice();
        } catch (Exception $e) {
            $this->addErrorLog('Invoice was not created. Reason: %msg%', array('msg' => $e->getMessage()));
        }

        if (!is_null($invoice)) {
            $this->addSuccessLog('Invoice #%invoice_id% was created.', array(
                '!invoice_id' => $invoice->getIncrementId()
            ));
        }

        return $invoice;
    }

    // ########################################

    public function createShipment()
    {
        $shipment = null;

        try {
            $shipment = $this->getChildObject()->createShipment();
        } catch (Exception $e) {
            $this->addErrorLog('Shipment was not created. Reason: %msg%', array('msg' => $e->getMessage()));
        }

        if (!is_null($shipment)) {
            $this->addSuccessLog('Shipment #%shipment_id% was created.', array(
                '!shipment_id' => $shipment->getIncrementId()
            ));
        }

        return $shipment;
    }

    // ########################################
}