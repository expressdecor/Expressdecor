<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_GeneralController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro');
    }

    //#############################################

    public function validationCheckRepetitionValueAction()
    {
        $model = $this->getRequest()->getParam('model','');

        $dataField = $this->getRequest()->getParam('data_field','');
        $dataValue = $this->getRequest()->getParam('data_value','');

        if ($model == '' || $dataField == '' || $dataValue == '') {
            exit(json_encode(array('result'=>false)));
        }

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();

        if ($dataField != '' && $dataValue != '') {
            $collection->addFieldToFilter($dataField, array('in'=>array($dataValue)));
        }

        $idField = $this->getRequest()->getParam('id_field','id');
        $idValue = $this->getRequest()->getParam('id_value','');

        if ($idField != '' && $idValue != '') {
            $collection->addFieldToFilter($idField, array('nin'=>array($idValue)));
        }

        exit(json_encode(array('result'=>!(bool)$collection->getSize())));
    }

    //#############################################

    public function synchCheckStateAction()
    {
        $lockItemModel = Mage::getModel('M2ePro/Synchronization_LockItem');

        if ($lockItemModel->isExist()) {
            exit('executing');
        }

        exit('inactive');
    }

    public function synchGetLastResultAction()
    {
        $logsModel = Mage::getModel('M2ePro/Synchronization_Log');
        $runsModel = Mage::getModel('M2ePro/Synchronization_Run');

        $tempCollection = $logsModel->getCollection();
        $tempCollection->addFieldToFilter('synchronization_run_id', (int)$runsModel->getLastId());
        $tempCollection->addFieldToFilter('type', array('in' => array(Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR)));

        if ($tempCollection->getSize() > 0) {
            exit('error');
        }

        $tempCollection = $logsModel->getCollection();
        $tempCollection->addFieldToFilter('synchronization_run_id', (int)$runsModel->getLastId());
        $tempCollection->addFieldToFilter('type', array('in' => array(Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING)));

        if ($tempCollection->getSize() > 0) {
            exit('warning');
        }

        exit('success');
    }

    public function synchGetExecutingInfoAction()
    {
        $response = array(
            'mode' => 'executing'
        );

        $lockItemModel = Mage::getModel('M2ePro/Synchronization_LockItem');

        if (!$lockItemModel->isExist()) {
            $response['mode'] = 'inactive';
            exit(json_encode($response));
        }

        $response['title'] = $lockItemModel->getContentData('info_title');

        $response['percents'] = (int)$lockItemModel->getContentData('info_percents');
        $response['percents'] < 0 && $response['percents'] = 0;

        $response['status'] = $lockItemModel->getContentData('info_status');

        exit(json_encode($response));
    }

    //#############################################

    public function modelGetAllAction()
    {
        $model = $this->getRequest()->getParam('model','');
        $componentMode = $this->getRequest()->getParam('component_mode', '');

        $idField = $this->getRequest()->getParam('id_field','id');
        $dataField = $this->getRequest()->getParam('data_field','');

        if ($model == '' || $idField == '' || $dataField == '') {
            exit(json_encode(array()));
        }

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();
        $componentMode != '' && $collection->addFieldToFilter('component_mode', $componentMode);

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                ->columns(array($idField, $dataField));

        $sortField = $this->getRequest()->getParam('sort_field','');
        $sortDir = $this->getRequest()->getParam('sort_dir','ASC');

        if ($sortField != '' && $sortDir != '') {
            $collection->setOrder('main_table.'.$sortField,$sortDir);
        }

        $limit = $this->getRequest()->getParam('limit',NULL);
        !is_null($limit) && $collection->setPageSize((int)$limit);

        $data = $collection->toArray();

        exit(json_encode($data['items']));
    }

    public function modelGetAllByAttributeSetIdAction()
    {
        $model = $this->getRequest()->getParam('model','');
        $componentMode = $this->getRequest()->getParam('component_mode', '');
        $attributeSets = $this->getRequest()->getParam('attribute_sets','');

        $idField = $this->getRequest()->getParam('id_field','id');
        $dataField = $this->getRequest()->getParam('data_field','');

        if ($model == '' || $attributeSets == '' || $idField == '' || $dataField == '') {
            exit(json_encode(array()));
        }

        $templateType = 0;
        switch ($model) {
            case 'Template_SellingFormat':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT;
                break;
            case 'Template_Description':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION;
                break;
            case 'Template_General':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL;
                break;
        }

        $tasTable = Mage::getResourceModel('M2ePro/AttributeSet')->getMainTable();

        $collection = Mage::getModel('M2ePro/'.$model)->getCollection();
        $componentMode != '' && $collection->addFieldToFilter('component_mode', $componentMode);

        $collection->getSelect()
                   ->join(array('tas'=>$tasTable),'`main_table`.`'.$idField.'` = `tas`.`object_id`',array())
                   ->where('`tas`.`object_type` = ?',(int)$templateType)
                   ->group('main_table.'.$idField)
                   ->having('COUNT(`main_table`.`'.$idField.'`) >= ?', count($attributeSets));

        $attributeSets = explode(',', $attributeSets);
        $collection->addFieldToFilter('`tas`.`attribute_set_id`', array('in' => $attributeSets));

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)
                                ->columns(array($idField, $dataField));

        $sortField = $this->getRequest()->getParam('sort_field','');
        $sortDir = $this->getRequest()->getParam('sort_dir','ASC');

        if ($sortField != '' && $sortDir != '') {
            $collection->setOrder('main_table.'.$sortField,$sortDir);
        }

        $limit = $this->getRequest()->getParam('limit',NULL);
        !is_null($limit) && $collection->setPageSize((int)$limit);

        $data = $collection->toArray();

        foreach ($data['items'] as $key => $value) {
            $data['items'][$key]['title'] = Mage::helper('M2ePro')->escapeHtml($data['items'][$key]['title']);
        }

        exit(json_encode($data['items']));
    }

    //#############################################

    public function searchAutocompleteAction()
    {
        $model       = $this->getRequest()->getParam('model');
        $component   = $this->getRequest()->getParam('component');
        $queryString = $this->getRequest()->getParam('query');
        $maxResults  = (int) $this->getRequest()->getParam('maxResults');

        if (!$model || !$component || !$queryString || !$maxResults) {
            exit(json_encode(array()));
        }

        $where = array();
        $parts = explode(' ', $queryString);
        foreach ($parts as $part) {
            $where[]['like'] = "%$part%";
        }

        $quotedQueryString = addslashes(trim($queryString));

        $relevanceQueryString  = "IF( `main_table`.`title` LIKE '%". $quotedQueryString. "%', ";
        $relevanceQueryString .= substr_count($quotedQueryString, " ") + 1;
        $relevanceQueryString .= "*3, 0) + IF( `main_table`.`title` LIKE '%";
        $relevanceQueryString .= str_replace(" ", "%', 1, 0) + IF( `main_table`.`title` LIKE '%", $quotedQueryString);
        $relevanceQueryString .= "%', 1 , 0)";

        $collection = Mage::helper('M2ePro/Component')
            ->getComponentModel($component, $model)
            ->getCollection()
            ->addFieldToFilter("`main_table`.`title`", $where)
            ->setOrder('relevance', 'DESC');

        $collection->getSelect()->columns(array('relevance' => new Zend_Db_Expr($relevanceQueryString)));

        $quantity = $collection->getSize();
        $collection->getSelect()->limit($maxResults);
        $results = $collection->getData();

        $suggestions = array();
        $ids         = array();

        foreach ($results as $result) {
            $suggestions[] = $result['title'];
            $ids[] = $result['id'];
        }
        $array = array(
            'query'       => $queryString,
            'suggestions' => $suggestions,
            'data'        => $ids,
            'quantity'    => $quantity
        );
        exit(json_encode($array));
    }

    public function searchAutocompleteByAttributeSetIdAction()
    {
        $idField     = $this->getRequest()->getParam('id_field','id');
        $model       = $this->getRequest()->getParam('model');
        $component   = $this->getRequest()->getParam('component');
        $queryString = $this->getRequest()->getParam('query');
        $maxResults  = (int) $this->getRequest()->getParam('maxResults');
        $attributeSets = $this->getRequest()->getParam('attribute_sets');

        if (!$model || !$component || !$queryString || !$maxResults || !$attributeSets) {
            exit(json_encode(array()));
        }

        $where = array();
        $parts = explode(' ', $queryString);
        foreach ($parts as $part) {
            $where[]['like'] = "%$part%";
        }

        $quotedQueryString = addslashes(trim($queryString));
        $relevanceQueryString  = "IF( `main_table`.`title` LIKE '%". $quotedQueryString. "%', ";
        $relevanceQueryString .= substr_count($quotedQueryString, " ") + 1;
        $relevanceQueryString .= "*3, 0) + IF( `main_table`.`title` LIKE '%";
        $relevanceQueryString .= str_replace(" ", "%', 1, 0) + IF( `main_table`.`title` LIKE '%", $quotedQueryString);
        $relevanceQueryString .= "%', 1 , 0)";

        $templateType = 0;
        switch ($model) {
            case 'Template_SellingFormat':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT;
                break;
            case 'Template_Description':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_DESCRIPTION;
                break;
            case 'Template_General':
                $templateType = Ess_M2ePro_Model_AttributeSet::OBJECT_TYPE_TEMPLATE_GENERAL;
                break;
        }

        $tasTable = Mage::getResourceModel('M2ePro/AttributeSet')->getMainTable();

        $collection = Mage::helper('M2ePro/Component')
            ->getComponentModel($component, $model)
            ->getCollection()
            ->addFieldToFilter("`main_table`.`title`", $where);

        $collection->getSelect()->columns(array('relevance' => new Zend_Db_Expr($relevanceQueryString)));

        $collection->getSelect()
            ->join(array('tas'=>$tasTable),'`main_table`.`'.$idField.'` = `tas`.`object_id`',array())
            ->where('`tas`.`object_type` = ?',(int)$templateType);

        $attributeSets = explode(',', $attributeSets);
        $collection->addFieldToFilter('`tas`.`attribute_set_id`', array('in' => $attributeSets));

        $collection->getSelect()
                   ->group('main_table.'.$idField)
                   ->having('COUNT(`main_table`.`'.$idField.'`) >= ?', count($attributeSets));

        $results = $collection->setOrder('relevance', 'DESC')->getData();
        $quantity = count($results);

        $suggestions = array();
        $ids         = array();

        $results = array_slice($results,0,$maxResults);

        foreach ($results as $result) {
            $suggestions[] = $result['title'];
            $ids[] = $result['id'];
        }
        $array = array(
            'query'       => $queryString,
            'suggestions' => $suggestions,
            'data'        => $ids,
            'quantity'    => $quantity
        );
        exit(json_encode($array));
    }

    //#############################################

    public function magentoGetAttributesByAttributeSetsAction()
    {
        $attributeSets = $this->getRequest()->getParam('attribute_sets','');

        if ($attributeSets == '') {
            exit(json_encode(array()));
        }

        $attributeSets = explode(',',$attributeSets);

        if (!is_array($attributeSets) || count($attributeSets) <= 0) {
            exit(json_encode(array()));
        }

        exit(json_encode(
            Mage::helper('M2ePro/Magento')->getAttributesByAttributeSets($attributeSets)
        ));
    }

    //#############################################
}