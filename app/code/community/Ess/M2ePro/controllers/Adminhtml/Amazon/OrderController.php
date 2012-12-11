<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Amazon_OrderController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/sales')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Sales'))
             ->_title(Mage::helper('M2ePro')->__('Amazon Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/OrderHandler.js')
             ->addJs('M2ePro/Order/Edit/ShippingAddressHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/sales/order');
    }

    //#############################################

    public function indexAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->_redirect('*/adminhtml_order/index');
        }

        /** @var $block Ess_M2ePro_Block_Adminhtml_Order */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order');
        $block->enableAmazonTab();

        $this->getResponse()->setBody($block->getAmazonTabHtml());
    }

    public function gridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_amazon_order_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $order);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_order_view'))
             ->renderLayout();
    }

    //#############################################

    public function orderItemGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

        if (!$id || !$order->getId()) {
            return;
        }

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $order);

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_amazon_order_view_item')->toHtml();
        $this->getResponse()->setBody($response);
    }

    public function orderLogGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $order);

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_amazon_order_view_log')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################

    public function createMagentoOrderAction()
    {
        $id = $this->getRequest()->getParam('id');
        $force = $this->getRequest()->getParam('force');

        /** @var $order Ess_M2ePro_Model_Order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

        if (!is_null($order->getMagentoOrderId()) && $force != 'yes') {
            $message = 'Magento Order is already created for this %channel% Order. ' .
                       'Press Create Order button to create new one.';

            $this->_getSession()->addWarning(str_replace(
                '%channel%', Ess_M2ePro_Helper_Component_Amazon::NICK, Mage::helper('M2ePro')->__($message)
            ));
            $this->_redirect('*/*/view', array('id' => $id));
            return;
        }

        // Create magento order
        // -------------
        try {
            $order->createMagentoOrder();
            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Magento Order was created.'));
        } catch (Exception $e) {
            $message = 'Magento Order was not created. Reason: %msg%';
            $message = Mage::helper('M2ePro')->__($message);
            $this->_getSession()->addError(str_replace(
                '%msg%', Mage::getSingleton('M2ePro/Log_Abstract')->decodeDescription($e->getMessage()), $message
            ));
        }
        // -------------

        // Create invoice
        // -------------
        if ($order->getChildObject()->canCreateInvoice()) {
            $result = $order->createInvoice();
            $result && $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Invoice was created.'));
        }
        // -------------

        // Create shipment
        // -------------
        if ($order->getChildObject()->canCreateShipment()) {
            $result = $order->createShipment();
            $result && $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Shipment was created.'));
        }
        // -------------

        $this->_redirect('*/*/view', array('id' => $id));
    }

    //#############################################

    public function editShippingAddressAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $order);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_order_edit_shippingAddress'))
             ->renderLayout();
    }

    public function saveShippingAddressAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/adminhtml_order/index');
        }

        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

        $data = array();
        $keys = array(
            'buyer_name',
            'buyer_email'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $order->setData('buyer_name', $data['buyer_name']);
        $order->setData('buyer_email', $data['buyer_email']);

        $data = array();
        $keys = array(
            'county',
            'country_code',
            'state',
            'city',
            'postal_code',
            'recipient_name',
            'phone',
            'street'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $order->setData('shipping_address', json_encode($data));
        $order->save();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Order address has been updated.'));

        $this->_redirect('*/adminhtml_amazon_order/view', array('id' => $order->getId()));
    }

    //#############################################

    public function updateShippingStatusAction()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var $order Ess_M2ePro_Model_Order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$id);

        if (!$order->getChildObject()->canUpdateShippingStatus()) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Amazon Order status cannot be updated to Shipped.')
            );
            return $this->_redirect('*/*/view', array('id' => $id));
        }

        if ($order->getChildObject()->updateShippingStatus()) {
            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('Updating Amazon Order Status to Shipped in Progress...')
            );
        } else {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Updating Amazon Order Status Failed.')
            );
        }

        return $this->_redirect('*/*/view', array('id' => $id));
    }

    //#############################################
}