<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_CmdController extends Ess_M2ePro_Controller_Adminhtml_CmdController
{
    //#############################################

    public function indexAction()
    {
        $this->printCommandsList();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro');
    }

    //#############################################

    /**
     * @title "Test"
     * @description "Command for quick development"
     * @group "Development"
     * @new_line
     */
    public function testAction()
    {
        $this->printBack();
    }

    /**
     * @title "Run Processing Cron"
     * @description "Run Processing Cron"
     * @group "Development"
     * @new_line
     */
    public function cronProcessingTemporaryAction()
    {
        $this->printBack();
        Mage::getModel('M2ePro/Processing_Cron')->process();
    }

    /**
     * @title "Check Upgrade to 3.2.0"
     * @description "Check extension installation"
     * @group "Development"
     * @confirm "Are you sure?"
     */
    public function checkInstallationCacheAction()
    {
        /** @var $installerInstance Ess_M2ePro_Model_Upgrade_MySqlSetup */
        $installerInstance = new Ess_M2ePro_Model_Upgrade_MySqlSetup('M2ePro_setup');

        /** @var $migrationInstance Ess_M2ePro_Model_Upgrade_Migration_ToVersion4 */
        $migrationInstance = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion4');
        $migrationInstance->setInstaller($installerInstance);

        $migrationInstance->startSetup();
        $migrationInstance->migrate();
        $migrationInstance->endSetup();

        Mage::helper('M2ePro/Magento')->clearCache();

        Mage::helper('M2ePro')->setSessionValue('success_message', 'Check installation was successfully completed.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    /**
     * @title "Repeat Upgrade > 3.2.0"
     * @description "Repeat Upgrade From Certain Version"
     * @group "Development"
     */
    public function recurringUpdateAction()
    {
        if ($this->getRequest()->getParam('upgrade')) {

            $version = $this->getRequest()->getParam('version');
            $version = str_replace(array(','),'.',$version);

            if (!version_compare('3.2.0',$version,'<=')) {
                Mage::helper('M2ePro')->setSessionValue(
                    'error_message', 'Extension upgrade can work only from 3.2.0 version.'
                );
                $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
                return;
            }

            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

            $coreResourceTable = Mage::getSingleton('core/resource')->getTableName('core_resource');
            $bind = array('version'=>$version,'data_version'=>$version);
            $connWrite->update($coreResourceTable,$bind,array('code = ?'=>'M2ePro_setup'));

            Mage::helper('M2ePro/Magento')->clearCache();

            Mage::helper('M2ePro')->setSessionValue('success_message', 'Extension upgrade was successfully completed.');
            $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));

            return;
        }

        $this->printBack();
        $urlPhpInfo = $this->getUrl('*/*/*', array('upgrade' => 'yes'));

        echo '<form method="GET" action="'.$urlPhpInfo.'">
                From version: <input type="text" name="version" value="3.2.0" />
                <input type="submit" title="Upgrade Now!" onclick="return confirm(\'Are you sure?\');" />
              </form>';
    }

    /**
     * @title "Remove Config Duplicates"
     * @description "Remove Configuration Duplicates"
     * @group "Development"
     * @confirm "Are you sure?"
     */
    public function removeConfigDuplicatesAction()
    {
        /** @var $installerInstance Ess_M2ePro_Model_Upgrade_MySqlSetup */
        $installerInstance = new Ess_M2ePro_Model_Upgrade_MySqlSetup('M2ePro_setup');
        $installerInstance->removeConfigDuplicates();

        Mage::helper('M2ePro/Module')->clearCache();

        Mage::helper('M2ePro')->setSessionValue('success_message', 'Remove duplicates was successfully completed.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    /**
     * @title "Check Server Connection"
     * @description "Send test request to server and check connection"
     * @group "Development"
     * @new_line
     */
    public function serverCheckConnectionAction()
    {
        $this->printBack();

        $curlObject = curl_init();

        //set the server we are using
        $serverUrl = Mage::helper('M2ePro/Connector_Server')->getScriptPath().'index.php';
        curl_setopt($curlObject, CURLOPT_URL, $serverUrl);

        // stop CURL from verifying the peer's certificate
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlObject, CURLOPT_SSL_VERIFYHOST, false);

        // disable http headers
        curl_setopt($curlObject, CURLOPT_HEADER, false);

        // set the data body of the request
        curl_setopt($curlObject, CURLOPT_POST, true);
        curl_setopt($curlObject, CURLOPT_POSTFIELDS, http_build_query(array(),'','&'));

        // set it to return the transfer as a string from curl_exec
        curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlObject, CURLOPT_CONNECTTIMEOUT, 300);

        $response = curl_exec($curlObject);

        echo '<h1>Response</h1><pre>';
        print_r($response);
        echo '</pre><h1>Report</h1><pre>';
        print_r(curl_getinfo($curlObject));
        echo '</pre>';

        echo '<h2 style="color:red;">Errors</h2>';
        echo curl_errno($curlObject) . ' ' . curl_error($curlObject) . '<br><br>';

        curl_close($curlObject);
    }

    

    

    //#############################################

    /**
     * @title "PHP Info"
     * @description "View server phpinfo() information"
     * @group "System"
     * @new_line
     */
    public function phpInfoAction()
    {
        if ($this->getRequest()->getParam('frame')) {
            phpinfo();
            return;
        }

        $this->printBack();
        $urlPhpInfo = $this->getUrl('*/*/*', array('frame' => 'yes'));
        echo '<iframe src="' . $urlPhpInfo . '" style="width:100%; height:90%;" frameborder="no"></iframe>';
    }

    /**
     * @title "ESS Configuration"
     * @description "Go to ess configuration edit page"
     * @group "System"
     */
    public function goToEditEssConfigAction()
    {
        $this->_redirect('*/adminhtml_config/ess');
    }

    /**
     * @title "M2ePro Configuration"
     * @description "Go to m2epro configuration edit page"
     * @group "System"
     * @new_line
     */
    public function goToEditM2eProConfigAction()
    {
        $this->_redirect('*/adminhtml_config/m2epro');
    }

    /**
     * @title "Run Cron"
     * @description "Emulate starting cron"
     * @group "System"
     */
    public function runCronAction()
    {
        Mage::getModel('M2ePro/Cron')->process();
    }

    /**
     * @title "Update License"
     * @description "Send update license request to server"
     * @group "System"
     * @new_line
     */
    public function licenseUpdateAction()
    {
        Mage::getModel('M2ePro/License_Server')->updateStatus(true);
        Mage::getModel('M2ePro/License_Server')->updateLock(true);
        Mage::getModel('M2ePro/License_Server')->updateMessages(true);

        Mage::helper('M2ePro')->setSessionValue('success_message', 'License status was successfully updated.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    /**
     * @title "Clear COOKIES"
     * @description "Clear all current cookies"
     * @group "System"
     * @confirm "Are you sure?"
     */
    public function clearCookiesAction()
    {
        foreach ($_COOKIE as $name => $value) {
            setcookie($name, '', 0, '/');
        }
        Mage::helper('M2ePro')->setSessionValue('success_message', 'Cookies was successfully cleared.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    /**
     * @title "Clear Extension Cache"
     * @description "Clear extension cache"
     * @group "System"
     * @confirm "Are you sure?"
     */
    public function clearExtensionCacheAction()
    {
        Mage::helper('M2ePro/Module')->clearCache();
        Mage::helper('M2ePro')->setSessionValue('success_message', 'Extension cache was successfully cleared.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    /**
     * @title "Clear Magento Cache"
     * @description "Clear magento cache"
     * @group "System"
     * @confirm "Are you sure?"
     */
    public function clearMagentoCacheAction()
    {
        Mage::helper('M2ePro/Magento')->clearCache();
        Mage::helper('M2ePro')->setSessionValue('success_message', 'Magento cache was successfully cleared.');
        $this->_redirectUrl($this->getUrl('*/adminhtml_cmd/index'));
    }

    //#############################################

    private function processSynchTasks($tasks)
    {
        $configProfiler = Mage::helper('M2ePro/Module')->getConfig()->getAllGroupValues('/synchronization/profiler/');

        if (count($configProfiler) > 0) {
            $shutdownFunctionCode = '';
            foreach ($configProfiler as $key => $value) {
                $shutdownFunctionCode .= "Mage::helper('M2ePro/Module')->getConfig()";
                $shutdownFunctionCode .= "->setGroupValue('/synchronization/profiler/', '{$key}', '{$value}');";
            }
            $shutdownFunctionInstance = create_function('', $shutdownFunctionCode);
            register_shutdown_function($shutdownFunctionInstance);
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/profiler/','mode','3');
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/profiler/','delete_resources','0');
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/profiler/','print_type','2');

        session_write_close();

        /** @var $synchDispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_DEVELOPER);
        $synchDispatcher->setComponents(array(
            //Ess_M2ePro_Helper_Component_Ebay::NICK,
            Ess_M2ePro_Helper_Component_Amazon::NICK
        ));
        $synchDispatcher->setTasks($tasks);
        $synchDispatcher->setParams(array());
        $synchDispatcher->process();

        if (count($configProfiler) > 0) {
            foreach ($configProfiler as $key => $value) {
                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/profiler/', $key, $value);
            }
        }
    }

    //---------------------------------------------

    /**
     * @title "Cron Tasks"
     * @description "Run all cron synchronization tasks as developer mode"
     * @group "Synchronization"
     * @confirm "Are you sure?"
     * @new_line
     */
    public function synchCronTasksAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
              Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES,
              Ess_M2ePro_Model_Synchronization_Tasks::ORDERS,
              Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS,
              Ess_M2ePro_Model_Synchronization_Tasks::MESSAGES,
              Ess_M2ePro_Model_Synchronization_Tasks::OTHER_LISTINGS
         ));
    }

    /**
     * @title "Defaults"
     * @description "Run only defaults synchronization as developer mode"
     * @group "Synchronization"
     * @confirm "Are you sure?"
     */
    public function synchDefaultsAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS
         ));
    }

    /**
     * @title "Templates"
     * @description "Run only templates synchronization as developer mode"
     * @group "Synchronization"
     * @confirm "Are you sure?"
     */
    public function synchTemplatesAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES
         ));
    }

    /**
     * @title "Orders"
     * @description "Run only orders synchronization as developer mode"
     * @group "Synchronization"
     * @confirm "Are you sure?"
     */
    public function synchOrdersAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::ORDERS
         ));
    }

    /**
     * @title "Feedbacks"
     * @description "Run only feedbacks synchronization as developer mode"
     * @group "Synchronization"
     * @confirm "Are you sure?"
     */
    public function synchFeedbacksAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS
         ));
    }

     /**
     * @title "Messages"
     * @description "Run only messages synchronization as developer mode"
     * @group "Synchronization"
     * @confirm "Are you sure?"
     */
    public function synchMessagesAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::MESSAGES
         ));
    }

    /**
     * @title "Marketplaces"
     * @description "Run only marketplaces synchronization as developer mode"
     * @group "Synchronization"
     * @confirm "Are you sure?"
     */
    public function synchMarketplacesAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::MARKETPLACES
         ));
    }

    /**
     * @title "3rd Party Listings"
     * @description "Run only 3rd party listings synchronization as developer mode"
     * @group "Synchronization"
     * @confirm "Are you sure?"
     */
    public function synchOtherListingsAction()
    {
        $this->printBack();
        $this->processSynchTasks(array(
              Ess_M2ePro_Model_Synchronization_Tasks::OTHER_LISTINGS
         ));
    }

    //#############################################
}