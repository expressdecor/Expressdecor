<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Log_Cron
{
    // ########################################

    public function process()
    {
        /** @var $tempModel Ess_M2ePro_Model_Log_Cleaning */
        $tempModel = Mage::getModel('M2ePro/Log_Cleaning');

        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS);
    }

    // ########################################
}