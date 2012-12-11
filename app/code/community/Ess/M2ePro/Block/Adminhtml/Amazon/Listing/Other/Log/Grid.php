<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Other_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        $amazonListingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        // Initialization block
        //------------------------------
        $this->setId('amazonListingOtherLogGrid'.(isset($amazonListingData['id'])?$amazonListingData['id']:''));
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    // ####################################

    protected function _prepareCollection()
    {
        $amazonListingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        // Get collection logs
        //--------------------------------
        $collection = Mage::getModel('M2ePro/Listing_Other_Log')->getCollection();
        $collection->getSelect()->where('`main_table`.component_mode = ? OR `main_table`.component_mode IS NULL',
                                        Ess_M2ePro_Helper_Component_Amazon::NICK);
        //--------------------------------

        // Join amazon_listings_table
        //--------------------------------
        $collection->getSelect()
                   ->joinLeft(array(
                       'lo' => Mage::getResourceModel('M2ePro/Listing_Other')->getMainTable()),
                       '(`main_table`.listing_other_id = `lo`.id)',
                       array(
                           'account_id'     => 'lo.account_id',
                           'marketplace_id' => 'lo.marketplace_id'
                       )
                   )
                   ->joinLeft(array(
                       'alo' => Mage::getResourceModel('M2ePro/Amazon_Listing_Other')->getMainTable()),
                       '(`main_table`.listing_other_id = `alo`.listing_other_id)',
                       array('general_id' => 'alo.general_id')
                   );
        //--------------------------------

        // Set listing filter
        //--------------------------------
        if (isset($amazonListingData['id'])) {
            $collection->addFieldToFilter('`main_table`.listing_other_id', $amazonListingData['id']);
        }
        //--------------------------------

        // we need sort by id also, because create_date may be same for some adjacents entries
        //--------------------------------
        if ($this->getRequest()->getParam('sort', 'create_date') == 'create_date') {
            $collection->setOrder('id', $this->getRequest()->getParam('dir', 'DESC'));
        }
        //--------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'width'     => '150px',
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date',
        ));

        $this->addColumn('general_id', array(
            'header' => Mage::helper('M2ePro')->__('ASIN / ISBN'),
            'align'  => 'left',
            'width'  => '100px',
            'type'   => 'text',
            'index'  => 'general_id',
            'filter_index' => 'alo.general_id',
            'frame_callback' => array($this, 'callbackColumnGeneralId')
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('M2ePro')->__('Product Name'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'title',
            'filter_index' => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('M2ePro')->__('Action'),
            'align'     => 'left',
            'width'     => '250px',
            'type'      => 'options',
            'index'     => 'action',
            'sortable'  => false,
            'filter_index' => 'main_table.action',
            'options' => Mage::getModel('M2ePro/Listing_Other_Log')->getActionsTitles()
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('M2ePro')->__('Description'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'description',
            'filter_index' => 'main_table.description',
            'frame_callback' => array($this, 'callbackDescription')
        ));

        $this->addColumn('type', array(
            'header'=> Mage::helper('M2ePro')->__('Type'),
            'width' => '80px',
            'index' => 'type',
            'align' => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->_getLogTypeList(),
            'frame_callback' => array($this, 'callbackColumnType')
        ));

        $this->addColumn('priority', array(
            'header'=> Mage::helper('M2ePro')->__('Priority'),
            'width' => '80px',
            'index' => 'priority',
            'align'     => 'right',
            'type'  => 'options',
            'sortable'  => false,
            'options' => $this->_getLogPriorityList(),
            'frame_callback' => array($this, 'callbackColumnPriority')
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        //--------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        //--------------------------------
    }

    // ####################################

    public function callbackColumnGeneralId($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else {
            $url = Mage::helper('M2ePro/Component_Amazon')->getItemUrl($row->getData('product_id'),
                                                                       $row->getData('marketplace_id'));
            $value = '<a href="' . $url . '" target="_blank">' . $value . '</a>';
        }

        return $value;
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        return Mage::helper('M2ePro')->escapeHtml($value);
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_log/listingOtherGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}