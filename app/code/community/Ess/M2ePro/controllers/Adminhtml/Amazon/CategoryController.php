<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Amazon_CategoryController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('m2epro/listings')
            ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Amazon Listings'));

        $this->getLayout()->getBlock('head')
            ->addItem('js_css', 'prototype/windows/themes/default.css')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addCss('M2ePro/css/Plugin/AutoComplete.css')
            ->addJs('prototype/window.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Amazon/Category/Handler.js')
            ->addJs('M2ePro/Amazon/Category/SpecificHandler.js');

        if (Mage::helper('M2ePro/Magento')->isCommunityEdition()) {
            version_compare(Mage::getVersion(), '1.7.0.0', '>=')
                ? $this->getLayout()->getBlock('head')->addCss('lib/prototype/windows/themes/magento.css')
                : $this->getLayout()->getBlock('head')->addItem('js_css', 'prototype/windows/themes/magento.css');
        } else {
            $this->getLayout()->getBlock('head')->addCss('lib/prototype/windows/themes/magento.css');
            $this->getLayout()->getBlock('head')->addItem('js_css', 'prototype/windows/themes/magento.css');
        }

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/listings/listing');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_category'))
             ->renderLayout();
    }

    public function categoryGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_amazon_category_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function addAction()
    {
        if ($this->getRequest()->isPost()) {
            return $this->_forward('save');
        }

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_category_edit'))
             ->renderLayout();
    }

    public function mapAction()
    {
        return $this->map(
            explode(',',$this->getRequest()->getParam('listing_product_ids')),
            $this->getRequest()->getParam('id')
        );
    }

    public function saveAction()
    {
        $post = $this->getRequest()->getPost();

        // Saving category info
        //----------------------------
        /** @var $amazonCategoryInstance Ess_M2ePro_Model_Amazon_Category */
        $amazonCategoryInstance = Mage::getModel('M2ePro/Amazon_Category');
        $post['category']['id'] && $amazonCategoryInstance->loadInstance((int)$post['category']['id']);

        $amazonCategoryInstance->addData(array(
            'marketplace_id' => (int)$this->getRequest()->getParam('marketplace_id'),
            'xsd_hash'       => $post['category']['xsd_hash'],
            'title'          => $post['category']['title'],
            'node_title'     => $post['category']['node_title'],
            'category_path'  => $post['category']['path']
        ));
        $amazonCategoryInstance->save();
        //----------------------------

        // Saving specifics info
        //----------------------------
        $amazonCategoryInstance->deleteSpecifics();
        //----------------------------

        $this->sort($post['specifics'],$post['category']['xsd_hash']);

        foreach ($post['specifics'] as $xpath => $specificData) {

            if (empty($specificData['mode'])) {
                continue;
            }
            if (empty($specificData['custom_value']) &&
                !in_array($specificData['mode'],array('none','custom_attribute'))) {
                continue;
            }
            if (empty($specificData['custom_attribute']) &&
                !in_array($specificData['mode'],array('none','custom_value'))) {
                continue;
            }

            /** @var $amazonCategorySpecificInstance Ess_M2ePro_Model_Amazon_Category_Specific */
            $amazonCategorySpecificInstance = Mage::getModel('M2ePro/Amazon_Category_Specific');

            $type = isset($specificData['type']) ? $specificData['type'] : '';
            $attributes = isset($specificData['attributes']) ? json_encode($specificData['attributes']) : '[]';
            $customValue = $specificData['mode'] == 'custom_value' ? $specificData['custom_value'] : '';
            $customAttribute = $specificData['mode'] == 'custom_attribute' ? $specificData['custom_attribute'] : '';

            $amazonCategorySpecificInstance->addData(array(
                'category_id'      => $amazonCategoryInstance->getId(),
                'xpath'            => $xpath,
                'mode'             => $specificData['mode'],
                'custom_value'     => $customValue,
                'custom_attribute' => $customAttribute,
                'type'             => $type,
                'attributes'       => $attributes
            ));
            $amazonCategorySpecificInstance->save();
        }
        //----------------------------

        if ($this->getRequest()->getParam('do_map')) {
            return $this->map(
                explode(',',$this->getRequest()->getParam('listing_product_ids')),
                $amazonCategoryInstance->getId()
            );
        }

        return $this->_redirect('*/adminhtml_amazon_category/index',array(
            'marketplace_id' => $this->getRequest()->getParam('marketplace_id'),
            'listing_product_ids' => $this->getRequest()->getParam('listing_product_ids')
        ));
    }

    public function editAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (!$id) {
            return '';
        }

        $amazonCategoryInstance = Mage::getModel('M2ePro/Amazon_Category')->loadInstance($id);

        $formData['category']  = $amazonCategoryInstance->getData();
        $formData['specifics'] = $amazonCategoryInstance->getSpecifics();

        Mage::helper('M2ePro')->setGlobalValue('temp_data',$formData);

        return $this->_forward('add');
    }

    public function deleteAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (!$id) {
            return '';
        }

        Mage::getModel('M2ePro/Amazon_Category')
            ->loadInstance($id)
            ->deleteInstance();

        return $this->_redirect('*/adminhtml_amazon_category/index',array(
            'marketplace_id' => $this->getRequest()->getParam('marketplace_id'),
            'listing_product_ids' => $this->getRequest()->getParam('listing_product_ids'),
            'back' => $this->getRequest()->getParam('back')
        ));
    }

    //#############################################

    public function getCategoriesAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $nodeHash = $this->getRequest()->getParam('node_hash');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category');

        exit(json_encode($connRead->select()
                                  ->from($table,'*')
                                  ->where('marketplace_id = ?', $marketplaceId)
                                  ->where('node_hash = ?', $nodeHash)
                                  ->query()
                                  ->fetchAll()));
    }

    //#############################################

    public function getSpecificsAction()
    {
        $tempSpecifics = $this->getSpecifics($this->getRequest()->getParam('xsd_hash'));

        $specifics = array();
        foreach ($tempSpecifics as $tempSpecific) {
            $specifics[$tempSpecific['id']] = $tempSpecific;
        }

        exit(json_encode($specifics));
    }

    //#############################################

    public function getXsdsAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $nodeHash = $this->getRequest()->getParam('node_hash');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_marketplace');

        $nodes = json_decode($connRead->select()
                                      ->from($table,'nodes')
                                      ->where('marketplace_id = ?', $marketplaceId)
                                      ->query()
                                      ->fetchColumn(),true);

        $xsds = array();
        foreach ($nodes as $node) {
            if ($node['hash'] == $nodeHash) {
                $xsds = $node['xsds'];
                break;
            }
        }

        exit(json_encode($xsds));
    }

    //#############################################

    public function checkRepetitionAction()
    {
        $id = $this->getRequest()->getParam('id');
        $title = $this->getRequest()->getParam('title');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        $collection = Mage::getModel('M2ePro/Amazon_Category')->getCollection();

        $collection->addFieldToFilter('title', $title);
        $collection->addFieldToFilter('marketplace_id', $marketplaceId);

        $id && $collection->addFieldToFilter('id', array('neq'=>$id));

        exit(json_encode(array('result'=>!(bool)$collection->getSize())));
    }

    //#############################################

    private function getSpecifics($xsdHash)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_specific');

        return $connRead->select()
                        ->from($table,'*')
                        ->where('xsd_hash = ?', $xsdHash)
                        ->query()
                        ->fetchAll();
    }

    //#############################################

    private function map($listingProductIds,$categoryId)
    {
        foreach ($listingProductIds as $listingProductId) {
            $listingProductInstance = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing_Product')
                                                                             ->loadInstance($listingProductId);
            $listingProductInstance->getChildObject()->setData('category_id',$categoryId)->save();
        }

        return $this->_redirect('*/adminhtml_amazon_listing/view',array(
            'id' => $listingProductInstance->getListingId()
        ));
    }

    //#############################################

    private function sort(&$specifics,$xsdHash)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $table =  Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_specific');

        $dictionarySpecifics = $connRead->select()
                                        ->from($table,'xpath')
                                        ->where('xsd_hash = ?',$xsdHash)
                                        ->query()
                                        ->fetchAll();

        $i = 0;
        foreach ($dictionarySpecifics as $key => $specific) {
            $xpath = $specific['xpath'];
            unset($dictionarySpecifics[$key]);
            $dictionarySpecifics[$xpath] = ++$i;
        }

        Mage::helper('M2ePro')->setGlobalValue('dictionary_specifics',$dictionarySpecifics);

        function callback($aXpath,$bXpath)
        {
            $dictionarySpecifics = Mage::helper('M2ePro')->getGlobalValue('dictionary_specifics');

            $aXpathParts = explode('/',$aXpath);
            foreach ($aXpathParts as &$part) {
                $part = preg_replace('/\-\d+$/','',$part);
            }
            unset($part);
            $aXpath = implode('/',$aXpathParts);

            $bXpathParts = explode('/',$bXpath);
            foreach ($bXpathParts as &$part) {
                $part = preg_replace('/\-\d+$/','',$part);
            }
            unset($part);
            $bXpath = implode('/',$bXpathParts);

            $aIndex = $dictionarySpecifics[$aXpath];
            $bIndex = $dictionarySpecifics[$bXpath];

            return $aIndex > $bIndex ? 1 : -1;
        }

        uksort($specifics,'callback');
    }

    //#############################################
}