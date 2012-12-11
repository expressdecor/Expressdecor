<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_OrderItem_Dispatcher extends Mage_Core_Model_Abstract
{
    const ACTION_ADD_DISPUTE = 1;

    // ########################################

    public function process($action, $items, array $params = array())
    {
        $items = $this->prepareItems($items);

        switch ($action) {
            case self::ACTION_ADD_DISPUTE:
                $result = $this->processItems(
                    $items, 'Ess_M2ePro_Model_Connector_Server_Ebay_OrderItem_Add_Dispute', $params
                );
                break;

            default;
                $result = false;
                break;
        }

        return $result;
    }

    // ########################################

    protected function processItems(array $items, $connectorName, array $params = array())
    {
        if (count($items) == 0) {
            return false;
        }

        /** @var $items Ess_M2ePro_Model_Order_Item[] */

        foreach ($items as $item) {

            try {
                $connector = new $connectorName($params, $item);
                if (!$connector->process()) {
                    return false;
                }
            } catch (Exception $e) {
                $item->getOrder()->addErrorLog(
                    'eBay Order Item action was not completed. Reason: %msg%', array('msg' => $e->getMessage())
                );

                return false;
            }

        }

        return true;
    }

    // ########################################

    private function prepareItems($items)
    {
        !is_array($items) && $items = array($items);

        $preparedItems = array();

        foreach ($items as $item) {
            if ($item instanceof Ess_M2ePro_Model_Order_Item) {
                $preparedItems[] = $item;
            } else if (is_numeric($item)) {
                $preparedItems[] = Mage::helper('M2ePro/Component_Ebay')->getObject('Order_Item', $item);
            }
        }

        return $preparedItems;
    }

    // ########################################
}