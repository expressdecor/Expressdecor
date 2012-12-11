<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_OrderController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/sales')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Sales'))
             ->_title(Mage::helper('M2ePro')->__('Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/OrderHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/sales/order');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_order'));

        $this->renderLayout();
    }

    //#############################################

    public function getCountryRegionsAction()
    {
        $country = $this->getRequest()->getParam('country');
        $regions = array();

        if (!empty($country)) {
            $regionsCollection = Mage::getResourceModel('directory/region_collection')
                ->addCountryFilter($country)
                ->load();

            foreach ($regionsCollection as $region) {
                $regions[] = array(
                    'value' => $region->getData('code'),
                    'label' => $region->getData('default_name')
                );
            }

            if (count($regions) > 0) {
                array_unshift($regions, array(
                    'value' => '',
                    'label' => Mage::helper('directory')->__('-- Please select --')
                ));
            }
        }

        exit(json_encode($regions));
    }

    //#############################################

    public function getDebugInformationAction()
    {
        $id = $this->getRequest()->getParam('id');

        if (is_null($id)) {
            return $this->getResponse()->setBody('');
        }

        try {
            $order = Mage::helper('M2ePro/Component')->getUnknownObject('Order', (int)$id);
        } catch (Exception $e) {
            return $this->getResponse()->setBody('');
        }

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $order);

        $debugBlock = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order_debug');
        $this->getResponse()->setBody($debugBlock->toHtml());
    }

    //#############################################

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');

        if (is_null($id)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Order ID is not defined.'));
            return $this->_redirect('*/*/index');
        }

        $order = Mage::getModel('M2ePro/Order')->load($id);

        if (is_null($order->getId())) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Order with such ID does not exist.'));
            return $this->_redirect('*/*/index');
        }

        $order->deleteInstance();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Order was successfully deleted.'));
        $this->_redirect('*/*/index');
    }

    //#############################################
}