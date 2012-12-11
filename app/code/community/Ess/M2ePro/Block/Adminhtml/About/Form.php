<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_About_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('aboutForm');
        //------------------------------

        $this->setTemplate('M2ePro/about.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // Set data for form
        //----------------------------
        $license['key'] = Mage::helper('M2ePro')->escapeHtml(Mage::getModel('M2ePro/License_Model')->getKey());

        $license['domain'] = Mage::getModel('M2ePro/License_Model')->getDomain();
        $license['ip'] = Mage::getModel('M2ePro/License_Model')->getIp();
        $license['directory'] = Mage::getModel('M2ePro/License_Model')->getDirectory();

        $license['components'] = array();
        foreach (Mage::helper('M2ePro/Component')->getAllowedComponents() as $component) {
            $license['components'][$component] = array(
                'mode' => Mage::getModel('M2ePro/License_Model')->getMode($component),
                'status' => Mage::getModel('M2ePro/License_Model')->getStatus($component),
                'expiration_date' => Mage::getModel('M2ePro/License_Model')->getTextExpirationDate($component)
            );
        }

        $this->license = $license;

        $system['name'] = Mage::helper('M2ePro/Server')->getSystem();

        $this->system = $system;

        $location['host'] = Mage::helper('M2ePro/Server')->getHost();
        $location['domain'] = Mage::helper('M2ePro/Server')->getDomain();
        $location['ip'] = Mage::helper('M2ePro/Server')->getIp();

        $this->location = $location;

        $platform['mode'] = Mage::helper('M2ePro')->__(ucwords(Mage::helper('M2ePro/Magento')->getEditionName()));
        $platform['version'] = Mage::helper('M2ePro/Magento')->getVersion();
        $platform['is_secret_key'] = Mage::helper('M2ePro/Magento')->isSecretKeyToUrl();

        $this->platform = $platform;

        $php['version'] = Mage::helper('M2ePro/Server')->getPhpVersion();
        $php['api'] = Mage::helper('M2ePro/Server')->getPhpApiName();
        $php['settings'] = Mage::helper('M2ePro/Server')->getPhpSettings();

        $this->php = $php;

        $mySql['database_name'] = Mage::helper('M2ePro/Magento')->getDatabaseName();
        $mySql['version'] = Mage::helper('M2ePro/Server')->getMysqlVersion();
        $mySql['api'] = Mage::helper('M2ePro/Server')->getMysqlApiName();
        $mySql['prefix'] = Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix();
        $mySql['settings'] = Mage::helper('M2ePro/Server')->getMysqlSettings();
        $mySql['total'] = Mage::helper('M2ePro/Server')->getMysqlTotals();

        $this->mySql = $mySql;

        $module['name'] = Mage::helper('M2ePro/Module')->getName();
        $module['version'] = Mage::helper('M2ePro/Module')->getVersion();
        $module['revision'] = Mage::helper('M2ePro/Module')->getRevision();
        $module['application_key'] = Mage::helper('M2ePro/Connector_Server')->getApplicationKey();

        $this->module = $module;

        //----------------------------
        $cron['php'] = 'php -q '. Mage::helper('M2ePro/Server')->getBaseDirectory() . DIRECTORY_SEPARATOR . 'cron.php';
        $cron['get'] = Mage::helper('M2ePro/Server')->getBaseUrl() .'cron.php';

        $cronLastAccessTime = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/', 'last_access');

        $cron['last_run'] = !is_null($cronLastAccessTime)
                                ? Mage::helper('M2ePro')->gmtDateToTimezone($cronLastAccessTime)
                                : 'N/A';

        $modelCron = Mage::getModel('M2ePro/Cron');

        $cron['last_run_highlight'] = 'none';
        if ($modelCron->isShowError()) {
            $cron['last_run_highlight'] = 'error';
        } else if ($modelCron->isShowNotification()) {
            $cron['last_run_highlight'] = 'warning';
        }

        $this->cron = $cron;
        //----------------------------

        $this->setChild('requirements', $this->getLayout()->createBlock('M2ePro/adminhtml_about_requirements'));

        $moduleDbTables = Mage::helper('M2ePro/Module')->getMySqlTables();
        $magentoDbTables = Mage::helper('M2ePro/Magento')->getMySqlTables();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $mysql['tables'] = array();
        foreach ($moduleDbTables as $moduleTable) {

            $failedTableComponent = false;
            foreach (Mage::helper('M2ePro/Component')->getForbiddenComponents() as $component) {
                if (strpos(strtolower($moduleTable),strtolower($component)) !== false) {
                    $failedTableComponent = true;
                    break;
                }
            }

            if ($failedTableComponent) {
                continue;
            }

            $arrayKey = $moduleTable;
            $arrayValue = array(
                'is_exist' => false,
                'count_items' => 0,
                'manage_link' => $this->getUrl('*/*/manageDbTable',array('table'=>$arrayKey)),
                'has_model' => false
            );

            // Find model
            //--------------------
            $tempModels = Mage::getConfig()->getNode('global/models/M2ePro_mysql4/entities');
            foreach ($tempModels->asArray() as $tempTable) {
                if ($tempTable['table'] == $arrayKey) {
                    $arrayValue['has_model'] = true;
                    break;
                }
            }
            //--------------------

            $moduleTable = Mage::getSingleton('core/resource')->getTableName($moduleTable);
            $arrayValue['is_exist'] = in_array($moduleTable, $magentoDbTables);

            if ($arrayValue['is_exist']) {
                $dbSelect = $connRead->select()->from($moduleTable,new Zend_Db_Expr('COUNT(*)'));
                $arrayValue['count_items'] = (int)$connRead->fetchOne($dbSelect);
            }

            //var_dump($arrayKey,$arrayValue);

            $mysql['tables'][$arrayKey] = $arrayValue;
        }

        $this->mysql = $mysql;
        //----------------------------

        //----------------------------
        $this->show_cmd = !is_null($this->getRequest()->getParam('show_cmd'));
        //----------------------------

        //----------------------------
        $this->isGoEdition = Mage::helper('M2ePro/Magento')->isGoEdition();
        //----------------------------

        return parent::_beforeToHtml();
    }
}