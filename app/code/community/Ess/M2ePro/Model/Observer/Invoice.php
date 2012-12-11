<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Invoice
{
    //####################################

    public function salesOrderInvoicePay(Varien_Event_Observer $observer)
    {
        try {

            if (Mage::helper('M2ePro')->getGlobalValue('skip_invoice_observer')) {
                // Not process invoice observer when set such flag
                Mage::helper('M2ePro')->unsetGlobalValue('skip_invoice_observer');
                return;
            }

            /** @var $invoice Mage_Sales_Model_Order_Invoice */
            $invoice = $observer->getEvent()->getInvoice();
            $magentoOrder = $invoice->getOrder();

            if (is_null($magentoOrderId = $magentoOrder->getData('entity_id'))) {
                return;
            }

            try {
                /** @var $loadedOrder Ess_M2ePro_Model_Order */
                $loadedOrder = Mage::helper('M2ePro/Component')
                    ->getUnknownObject('Order', $magentoOrderId, 'magento_order_id');
            } catch (Exception $e) {
                return;
            }

            if ($loadedOrder->isComponentModeAmazon()) {
                return;
            }

            $result = $loadedOrder->getChildObject()->updatePaymentStatus();

            $result ? $this->addSessionSuccessMessage()
                    : $this->addSessionErrorMessage($loadedOrder);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Exception')->process($exception,true);
            return;
        }
    }

    //####################################

    private function addSessionSuccessMessage()
    {
        $message = Mage::helper('M2ePro')->__('Payment Status for eBay Order was updated to Paid.');
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    private function addSessionErrorMessage(Ess_M2ePro_Model_Order $order)
    {
        $url = Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_ebay_order/view', array('id' => $order->getId()));

        $startLink = '<a href="' . $url . '" target="_blank">';
        $endLink = '</a>';
        $message  = Mage::helper('M2ePro')->__(
            'Payment Status for eBay Order was not updated. View %sl%order log%el% for more details.'
        );

        Mage::getSingleton('adminhtml/session')->addError(str_replace(
            array('%sl%', '%el%'), array($startLink, $endLink), $message
        ));
    }

    //####################################
}