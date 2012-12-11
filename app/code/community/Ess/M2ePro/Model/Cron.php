<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Cron
{
    const TASK_PROCESSING = 'processing';
    const TASK_LOGS_CLEANING = 'logs_cleaning';
    const TASK_SYNCHRONIZATION = 'synchronization';

    //####################################

    public function process()
    {
        // Recurring cron start
        //----------------------
        if (!is_null(Mage::helper('M2ePro')->getGlobalValue('cron_running'))) {
            return;
        }
        Mage::helper('M2ePro')->setGlobalValue('cron_running',true);
        //----------------------

        // Check cron mode
        //----------------------
        if (!Mage::helper('M2ePro/Wizard')->isInstallationFinished()) {
            return;
        }
        if (!(bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/', 'mode')) {
            return;
        }
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cron/', 'last_access',
                                                                  Mage::helper('M2ePro')->getCurrentGmtDate());
        //----------------------

        // Run cron tasks
        //----------------------
        $this->processProcessing();
        $this->processLogsCleaning();
        $this->processSynchronization();
        //----------------------
    }

    //------------------------------------

    public function isShowError()
    {
        if (!Mage::helper('M2ePro/Wizard')->isInstallationFinished()) {
            return false;
        }

        if (!(bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/', 'mode')) {
            return false;
        }

        if (!(bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/error/', 'mode')) {
            return false;
        }

        if (!Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            return false;
        }

        $cronLastAccessTime = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/', 'last_access');

        if (is_null($cronLastAccessTime)) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cron/', 'last_access',
                                                                      Mage::helper('M2ePro')->getCurrentGmtDate());
            return false;
        }

        $allowedInactiveHours = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/cron/error/', 'max_inactive_hours'
        );

        $temp = (strtotime($cronLastAccessTime) + ($allowedInactiveHours * 60*60));
        if (Mage::helper('M2ePro')->getCurrentGmtDate(true) > $temp) {
            return true;
        }

        return false;
    }

    public function isShowNotification()
    {
        if (!Mage::helper('M2ePro/Wizard')->isInstallationFinished()) {
            return false;
        }

        if (!(bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/', 'mode')) {
            return false;
        }

        if (!(bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/notification/', 'mode')) {
            return false;
        }

        $cronLastAccessTime = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/', 'last_access');

        if (is_null($cronLastAccessTime)) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cron/', 'last_access',
                                                                      Mage::helper('M2ePro')->getCurrentGmtDate());
            return false;
        }

        $allowedInactiveHours = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/cron/notification/', 'max_inactive_hours'
        );

        $temp = (strtotime($cronLastAccessTime) + ($allowedInactiveHours * 60*60));
        if (Mage::helper('M2ePro')->getCurrentGmtDate(true) > $temp) {
            return true;
        }

        return false;
    }

    //####################################

    private function processProcessing()
    {
        if (!$this->isNowTimeToRun(self::TASK_PROCESSING)) {
            return;
        }

        try {
            Mage::getModel('M2ePro/Processing_Cron')->process();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Exception')->process($exception,true);
        }
    }

    private function processLogsCleaning()
    {
        if (!$this->isNowTimeToRun(self::TASK_LOGS_CLEANING)) {
            return;
        }

        try {
            Mage::getModel('M2ePro/Log_Cron')->process();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Exception')->process($exception,true);
        }
    }

    private function processSynchronization()
    {
        if (!$this->isNowTimeToRun(self::TASK_SYNCHRONIZATION)) {
            return;
        }

        try {
            Mage::getModel('M2ePro/Synchronization_Cron')->process();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Exception')->process($exception,true);
        }
    }

    //####################################

    private function isNowTimeToRun($task)
    {
        if (!$this->isModeEnable($task)) {
            return false;
        }

        $interval = $this->getIntervalRuns($task);

        $lastAccess = $this->getLastAccessTime($task);
        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        if (is_null($lastAccess) || $currentTimeStamp > strtotime($lastAccess) + $interval) {
            $this->updateLastAccessTime($task);
            return true;
        }

        return false;
    }

    //------------------------------------

    private function isModeEnable($task)
    {
        $tempGroup = '/cron/task/'.$task.'/';
        return (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($tempGroup,'mode');
    }

    private function getIntervalRuns($task)
    {
        $tempGroup = '/cron/task/'.$task.'/';
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($tempGroup,'interval');
    }

    //------------------------------------

    private function getLastAccessTime($task)
    {
        $tempGroup = '/cron/task/'.$task.'/';
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($tempGroup,'last_access');
    }

    private function updateLastAccessTime($task)
    {
        $tempGroup = '/cron/task/'.$task.'/';
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue($tempGroup,'last_access',
                                                                  Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    //####################################
}