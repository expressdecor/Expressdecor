<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Amazon_Orders_Update_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Server_Amazon_Responser
{
    // Parser hack -> Mage::helper('M2ePro')->__('Amazon Order status was not updated. Reason: %msg%');
    // Parser hack -> Mage::helper('M2ePro')->__('Amazon Order status was updated to Shipped.');
    // Parser hack -> Mage::helper('M2ePro')->__('Tracking number "%num%" for "%code%" has been sent to Amazon.');

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        $this->getOrder()->deleteObjectLocks('update_shipping_status', $this->hash);
    }

    // ########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function processResponseData($response)
    {
        $hasError = false;
        $messages = !empty($response['messages']) ? $response['messages'] : array();

        foreach ($messages as $message) {
            $messageType = $message[Ess_M2ePro_Model_Connector_Server_Protocol::MESSAGE_TYPE_KEY];
            $messageText = $message[Ess_M2ePro_Model_Connector_Server_Protocol::MESSAGE_TEXT_KEY];

            if ($messageType == Ess_M2ePro_Model_Connector_Server_Protocol::MESSAGE_TYPE_ERROR) {
                $this->getOrder()->addErrorLog(
                    'Amazon Order status was not updated. Reason: %msg%', array('msg' => $messageText)
                );
                $hasError = true;
            }
        }

        if ($hasError) {
            return false;
        }

        $this->getOrder()->setData('status', Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED)->save();
        $this->getOrder()->addSuccessLog('Amazon Order status was updated to Shipped.');

        if (!empty($this->params['tracking_details'])) {
            $this->getOrder()->addSuccessLog(
                'Tracking number "%num%" for "%code%" has been sent to Amazon.', array(
                    '!num' => $this->params['tracking_details']['tracking_number'],
                    'code' => $this->params['tracking_details']['carrier_name']
                )
            );
        }

        return array();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Order
     */
    private function getOrder()
    {
        return $this->getObjectByParam('Order', 'order_id');
    }

    // ########################################
}