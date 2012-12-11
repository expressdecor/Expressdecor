<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Order_Log extends Mage_Core_Model_Abstract
{
    const TYPE_SUCCESS = 0;
    const TYPE_NOTICE  = 1;
    const TYPE_ERROR   = 2;
    const TYPE_WARNING = 3;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Log');
    }

    // ########################################

    public function add($componentMode, $orderId, $message, $type)
    {
        if (!in_array($type, array(self::TYPE_SUCCESS, self::TYPE_NOTICE, self::TYPE_ERROR, self::TYPE_WARNING))) {
            throw new LogicException('Invalid order log type.');
        }

        $log = array(
            'component_mode' => $componentMode,
            'order_id'       => $orderId,
            'message'        => $message,
            'type'           => (int)$type,
        );

        $this->setId(null)
             ->setData($log)
             ->save();
    }

    // ########################################

    public function deleteInstance()
    {
        return parent::delete();
    }

    // ########################################
}