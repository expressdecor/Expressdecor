<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Amazon_Template_DescriptionController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/templates')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Templates'))
             ->_title(Mage::helper('M2ePro')->__('Description Templates'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Template/AttributeSetHandler.js')
             ->addJs('M2ePro/Amazon/Template/DescriptionHandler.js');

        if (Mage::helper('M2ePro/Magento')->isTinyMceAvailable()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/templates/description');
    }

    //#############################################

    public function indexAction()
    {
        $this->_redirect('*/adminhtml_template_description/index');
    }

    //#############################################

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id    = $this->getRequest()->getParam('id');
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_Description')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Template does not exist'));
            return $this->_redirect('*/adminhtml_template_description/index');
        }

        $temp = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION;
        $templateAttributeSetsCollection = Mage::getModel('M2ePro/AttributeSet')->getCollection();
        $templateAttributeSetsCollection->addFieldToFilter('object_id', $id)
                                        ->addFieldToFilter('object_type', $temp);

        $templateAttributeSetsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                                     ->columns('attribute_set_id');

        $model->setData('attribute_sets', $templateAttributeSetsCollection->getColumnValues('attribute_set_id'));

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_template_description_edit'))
             ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/adminhtml_template_description/index');
        }

        $id = $this->getRequest()->getParam('id');

        // Base prepare
        //--------------------
        $data = array();

        $keys = array(
            'title',

            'title_mode',
            'title_template',
            'brand_mode',
            'brand_template',
            'manufacturer_mode',
            'manufacturer_template',

            'image_main_mode',
            'image_main_attribute',
            'gallery_images_mode',

            'bullet_points_mode',
            'bullet_points',

            'description_mode',
            'description_template',
            'editor_type'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $data['title'] = strip_tags($data['title']);
        $data['bullet_points'] = json_encode(array_filter($post['bullet_points']));
        //--------------------

        // Add or update model
        //--------------------
        $model = Mage::helper('M2ePro/Component_Amazon')->getModel('Template_Description');
        is_null($id) && $model->setData($data);
        !is_null($id) && $model->load($id)->addData($data);
        $id = $model->save()->getId();
        //--------------------

        // Attribute sets
        //--------------------
        $temp = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION;
        $oldAttributeSets = Mage::getModel('M2ePro/AttributeSet')
                                    ->getCollection()
                                    ->addFieldToFilter('object_type',$temp)
                                    ->addFieldToFilter('object_id',(int)$id)
                                    ->getItems();
        foreach ($oldAttributeSets as $oldAttributeSet) {
            /** @var $oldAttributeSet Ess_M2ePro_Model_AttributeSet */
            $oldAttributeSet->deleteInstance();
        }

        if (!is_array($post['attribute_sets'])) {
            $post['attribute_sets'] = explode(',', $post['attribute_sets']);
        }
        foreach ($post['attribute_sets'] as $newAttributeSet) {
            $dataForAdd = array(
                'object_type' => Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION,
                'object_id' => (int)$id,
                'attribute_set_id' => (int)$newAttributeSet
            );
            Mage::getModel('M2ePro/AttributeSet')->setData($dataForAdd)->save();
        }
        //--------------------

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Template was successfully saved'));
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('list',array(),array('edit'=>array('id'=>$id))));
    }

    //#############################################
}