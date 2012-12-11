<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Amazon_Orders_Update_Items
    extends Ess_M2ePro_Model_Connector_Server_Amazon_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('orders','update','entities');
    }

    // ########################################

    protected function getResponserModel()
    {
        return 'Amazon_Orders_Update_ItemsResponser';
    }

    protected function getResponserParams()
    {
        return array(
            'order_id' => $this->params['order']->getId(),
            'tracking_details' => isset($this->params['tracking_details']) ? $this->params['tracking_details'] : array()
        );
    }

    // ########################################

    protected function setLocks($hash)
    {
        $this->params['order']->addObjectLock('update_shipping_status', $hash);
    }

    // ########################################

    protected function getRequestData()
    {
        $itemTrackingDetails = isset($this->params['tracking_details']) ? $this->params['tracking_details'] : array();

        $fulfillmentDate = new DateTime('now', new DateTimeZone('UTC'));
        $fulfillmentDate->modify('-10 minutes');

        $item = array(
            'id' => $this->params['order']->getId(),
            'order_id' => $this->params['amazon_order_id'],
            'fulfillment_date' => $fulfillmentDate->format('c')
        );

        if (!empty($itemTrackingDetails)) {
            $item['carrier_name'] = $itemTrackingDetails['carrier_name'];
            $item['tracking_number'] = $itemTrackingDetails['tracking_number'];

            if (!empty($itemTrackingDetails['shipping_method'])) {
                $item['shipping_method'] = $itemTrackingDetails['shipping_method'];
            }
        }

        return array(
            'items' => array(
                $item
            )
        );

    }

    // ########################################
}