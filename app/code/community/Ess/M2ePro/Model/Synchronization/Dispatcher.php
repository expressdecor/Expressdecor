<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Dispatcher extends Ess_M2ePro_Model_Synchronization_Dispatcher_Abstract
{
    private $startTime = NULL;

    /**
     * @var array
     */
    private $_components = array();

    //####################################

    public function process()
    {
        // Check global mode
        //----------------------------------
        if (!(bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/','mode')) {
            return false;
        }
        //----------------------------------

        // Before dispatch actions
        //---------------------------
        if (!$this->beforeDispatch()) {
            return false;
        }
        //---------------------------

        try {

            // DEFAULTS SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS);
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                '/synchronization/settings/defaults/','mode'
            );
            if ($tempTask && $tempGlobalMode) {
                $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Defaults();
                $tempSynch->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // EBAY SYNCH
            //---------------------------
            if (Mage::helper('M2ePro/Component_Ebay')->isActive() &&
                $this->checkComponent(Ess_M2ePro_Helper_Component_Ebay::NICK)) {

                $synchDispatcher = Mage::getModel('M2ePro/Ebay_Synchronization_Dispatcher');
                $synchDispatcher->setInitiator($this->_initiator);
                $synchDispatcher->setTasks($this->_tasks);
                $synchDispatcher->setParams($this->_params);
                $synchDispatcher->process();
            }

            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // AMAZON SYNCH
            //---------------------------
            if (Mage::helper('M2ePro/Component_Amazon')->isActive() &&
                $this->checkComponent(Ess_M2ePro_Helper_Component_Amazon::NICK)) {

                $synchDispatcher = Mage::getModel('M2ePro/Amazon_Synchronization_Dispatcher');
                $synchDispatcher->setInitiator($this->_initiator);
                $synchDispatcher->setTasks($this->_tasks);
                $synchDispatcher->setParams($this->_params);
                $synchDispatcher->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        // After dispatch actions
        //---------------------------
        if (!$this->afterDispatch()) {
            return false;
        }
        //---------------------------

        return true;
    }

    //####################################

    public function setComponents(array $components = array())
    {
        $this->_components = array();

        foreach ($components as $component) {
            if ($component !== Ess_M2ePro_Helper_Component_Ebay::NICK &&
                $component !== Ess_M2ePro_Helper_Component_Amazon::NICK) {
                    continue;
            }
            $this->_components[] = $component;
        }
    }

    private function checkComponent($component)
    {
        return in_array($component, $this->_components);
    }

    //------------------------------------

    private function beforeDispatch()
    {
        // Save start time stamp
        $this->startTime = Mage::helper('M2ePro')->getCurrentGmtDate();

        // Create and save tasks
        //----------------------------------
        Mage::helper('M2ePro')->setGlobalValue('synchTasks',$this->_tasks);
        //----------------------------------

        // Create and save initiator
        //----------------------------------
        Mage::helper('M2ePro')->setGlobalValue('synchInitiator',$this->_initiator);
        //----------------------------------

        // Create and save initiator
        //----------------------------------
        Mage::helper('M2ePro')->setGlobalValue('synchParams',$this->_params);
        //----------------------------------

        // Create and save profiler
        //----------------------------------
        $profilerParams = array();

        if ($this->_initiator == Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER) {
            $profilerParams['muteOutput'] = true;
        } else {
            $profilerParams['muteOutput'] = false;
        }

        $profiler = Mage::getModel('M2ePro/Synchronization_Profiler',$profilerParams);
        Mage::helper('M2ePro')->setGlobalValue('synchProfiler',$profiler);

        Mage::helper('M2ePro')->getGlobalValue('synchProfiler')->enable();
        Mage::helper('M2ePro')->getGlobalValue('synchProfiler')->start();
        Mage::helper('M2ePro')->getGlobalValue('synchProfiler')->makeShutdownFunction();

        Mage::helper('M2ePro')->getGlobalValue('synchProfiler')->setClearResources();
        //----------------------------------

        // Create and save synch session
        //----------------------------------
        $runs = Mage::getModel('M2ePro/Synchronization_Run');
        Mage::helper('M2ePro')->setGlobalValue('synchRun',$runs);

        Mage::helper('M2ePro')->getGlobalValue('synchRun')->start($this->_initiator);
        Mage::helper('M2ePro')->getGlobalValue('synchRun')->makeShutdownFunction();
        Mage::helper('M2ePro')->getGlobalValue('synchRun')->cleanOldData();

        Mage::helper('M2ePro')->setGlobalValue(
            'synchId',Mage::helper('M2ePro')->getGlobalValue('synchRun')->getLastId()
        );
        //----------------------------------

        // Create and save logs
        //----------------------------------
        $logs = Mage::getModel('M2ePro/Synchronization_Log');
        Mage::helper('M2ePro')->setGlobalValue('synchLogs',$logs);

        Mage::helper('M2ePro')->getGlobalValue('synchLogs')->setSynchronizationRun(
            Mage::helper('M2ePro')->getGlobalValue('synchId')
        );
        Mage::helper('M2ePro')->getGlobalValue('synchLogs')->setSynchronizationTask(
            Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN
        );
        Mage::helper('M2ePro')->getGlobalValue('synchLogs')->setInitiator($this->_initiator);
        //----------------------------------

        // Create and save lock item
        //----------------------------------
        $lockItem = Mage::getModel('M2ePro/Synchronization_LockItem');
        Mage::helper('M2ePro')->setGlobalValue('synchLockItem',$lockItem);

        if (Mage::helper('M2ePro')->getGlobalValue('synchLockItem')->isExist()) {

            Mage::helper('M2ePro')->getGlobalValue('synchLogs')->addMessage(
                Mage::helper('M2ePro')->__('Another Synchronization Is Already Running'),
                Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
            Mage::helper('M2ePro')->getGlobalValue('synchProfiler')->addTitle(
                'Another Synchronization Is Already Running.',Ess_M2ePro_Model_General_Profiler::TYPE_ERROR
            );
            return false;
        }

        Mage::helper('M2ePro')->getGlobalValue('synchLockItem')->setSynchRunObj($runs);
        Mage::helper('M2ePro')->getGlobalValue('synchLockItem')->create();
        Mage::helper('M2ePro')->getGlobalValue('synchLockItem')->makeShutdownFunction();
        //----------------------------------

        // Try set memory limit
        $this->setMemoryLimit();

        // Make shutdown function for clearing product changes
        $this->makeShutdownFunctionForProductChanges();

        return true;
    }

    private function afterDispatch()
    {
        Mage::helper('M2ePro')->getGlobalValue('synchRun')->stop();
        Mage::helper('M2ePro')->getGlobalValue('synchProfiler')->stop();
        Mage::helper('M2ePro')->getGlobalValue('synchLockItem')->remove();

        Mage::getModel('M2ePro/ProductChange')->clearAll(
            Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,$this->startTime
        );
        Mage::getModel('M2ePro/ProductChange')->clearAll(
            Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION
        );

        return true;
    }

    //------------------------------------

    private function setMemoryLimit()
    {
        if (!(bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/memory/','mode')) {
            return false;
        }

        $minSize = 32;
        $maxSize = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/synchronization/memory/','max_size'
        );

        $currentMemoryLimit = Mage::helper('M2ePro/Server')->getMemoryLimit();

        if ($maxSize < $minSize || (int)$currentMemoryLimit >= $maxSize) {
            return false;
        }

        for ($i=$minSize; $i<=$maxSize; $i*=2) {

            if (@ini_set('memory_limit',"{$i}M") === false) {
                if ($i == $minSize) {
                    return false;
                } else {
                    return $i/2;
                }
            }
        }

        return true;
    }

    private function makeShutdownFunctionForProductChanges()
    {
        $functionCode = "Mage::getModel('M2ePro/ProductChange')";
        $functionCode .= "->clearAll(Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,'".$this->startTime."');";
        $functionCode .= "Mage::getModel('M2ePro/ProductChange')";
        $functionCode .= "->clearAll(Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION);";

        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);

        return true;
    }

    //####################################
}