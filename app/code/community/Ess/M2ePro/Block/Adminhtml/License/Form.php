<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_License_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('licenseForm');
        //------------------------------

        $this->setTemplate('M2ePro/license.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/confirmKey'),
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
        $this->key = Mage::helper('M2ePro')->escapeHtml(Mage::getModel('M2ePro/License_Model')->getKey());

        $valid = array();
        $valid['domain'] = Mage::getModel('M2ePro/License_Model')->getDomain();
        $valid['ip'] = Mage::getModel('M2ePro/License_Model')->getIp();
        $valid['directory'] = Mage::getModel('M2ePro/License_Model')->getDirectory();

        $this->valid = $valid;

        $components = array();
        foreach (Mage::helper('M2ePro/Component')->getAllowedComponents() as $component) {
            $components[$component] = array(
                'mode' => Mage::getModel('M2ePro/License_Model')->getMode($component),
                'status' => Mage::getModel('M2ePro/License_Model')->getStatus($component),
                'expiration_date' => Mage::getModel('M2ePro/License_Model')->getTextExpirationDate($component)
            );
        }

        $this->components = $components;
        //----------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh'),
                                'onclick' => 'setLocation(\''.$this->getUrl('*/*/refreshStatus').'\');',
                                'class' => 'refresh_status'
                            ) );
        $this->setChild('refresh_status',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Enter'),
                                'onclick' => 'LicenseHandlerObj.changeLicenseKey();',
                                'class' => 'enter_key'
                            ) );
        $this->setChild('enter_key',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Change'),
                                'onclick' => 'LicenseHandlerObj.changeLicenseKey();',
                                'class' => 'change_key'
                            ) );
        $this->setChild('change_key',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'LicenseHandlerObj.save_click(\''.$this->getUrl('*/*/confirmKey').'\');',
                                'class' => 'confirm_key'
                            ) );
        $this->setChild('confirm_key',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}