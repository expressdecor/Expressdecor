<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Log_Cleaning_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingLogCleaningForm');
        //------------------------------

        $this->setTemplate('M2ePro/listing/log/cleaning.phtml');
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
        //----------------------------
        $modes = array();

        $modes[Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS] = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS.'/','mode');
        $modes[Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS] = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS.'/','mode');
        $modes[Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS] = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS.'/','mode');

        $this->modes = $modes;

        $days = array();

        $days[Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS] = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS.'/','days');
        $days[Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS] = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS.'/','days');
        $days[Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS] = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/logs/cleaning/'.Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS.'/','days');

        $this->days = $days;
        //----------------------------

        //-------------------------------
        $temp = Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS;
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                                'onclick' => 'LogCleaningHandlerObj.runNowLog(\''.$temp.'\')',
                                'class' => 'run_now_'.Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS
                            ) );
        $this->setChild('run_now_'.Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS,$buttonBlock);

        $temp = Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS;
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Clear All'),
                                'onclick' => 'LogCleaningHandlerObj.clearAllLog(\''.$temp.'\')',
                                'class' => 'clear_all_'.Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS
                            ) );
        $this->setChild('clear_all_'.Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS,$buttonBlock);

        $url = $this->getUrl(
            '*/adminhtml_log/listing',
            array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_logCleaning/index'))
        );
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('View Log'),
                                'onclick' => 'setLocation(\''.$url.'\')',
                                'class' => 'button_link'
                            ) );
        $this->setChild('view_log_'.Ess_M2ePro_Model_Log_Cleaning::LOG_LISTINGS,$buttonBlock);
        //-------------------------------

        //-------------------------------
        $temp = Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS;
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                                'onclick' => 'LogCleaningHandlerObj.runNowLog(\''.$temp.'\')',
                                'class' => 'run_now_'.Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS
                            ) );
        $this->setChild('run_now_'.Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS,$buttonBlock);

        $temp = Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS;
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Clear All'),
                                'onclick' => 'LogCleaningHandlerObj.clearAllLog(\''.$temp.'\')',
                                'class' => 'clear_all_'.Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS
                            ) );
        $this->setChild('clear_all_'.Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS,$buttonBlock);

        $url = $this->getUrl(
            '*/adminhtml_log/listingOther',
            array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_logCleaning/index'))
        );
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('View Log'),
                                'onclick' => 'setLocation(\''.$url.'\')',
                                'class' => 'button_link'
                            ) );
        $this->setChild('view_log_'.Ess_M2ePro_Model_Log_Cleaning::LOG_OTHER_LISTINGS,$buttonBlock);
        //-------------------------------

        //-------------------------------
        $temp = Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS;
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Run Now'),
                                'onclick' => 'LogCleaningHandlerObj.runNowLog(\''.$temp.'\')',
                                'class' => 'run_now_'.Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS
                            ) );
        $this->setChild('run_now_'.Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS,$buttonBlock);

        $temp = Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS;
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Clear All'),
                                'onclick' => 'LogCleaningHandlerObj.clearAllLog(\''.$temp.'\')',
                                'class' => 'clear_all_'.Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS
                            ) );
        $this->setChild('clear_all_'.Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS,$buttonBlock);

        $url = $this->getUrl(
            '*/adminhtml_log/synchronization',
            array('back'=>Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_logCleaning/index'))
        );
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('View Log'),
                                'onclick' => 'setLocation(\''.$url.'\')',
                                'class' => 'button_link'
                            ) );
        $this->setChild('view_log_'.Ess_M2ePro_Model_Log_Cleaning::LOG_SYNCHRONIZATIONS,$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}