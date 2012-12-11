<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Log_Grid_Abstract
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        $ebayListingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        // Initialization block
        //------------------------------
        $this->setId('ebayListingOtherLogGrid'.(isset($ebayListingData['id'])?$ebayListingData['id']:''));
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
        $ebayListingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        // Get collection logs
        //--------------------------------
        $collection = Mage::getModel('M2ePro/Listing_Other_Log')->getCollection();
        $collection->getSelect()->where('`main_table`.component_mode = ? OR `main_table`.component_mode IS NULL',
                                        Ess_M2ePro_Helper_Component_Ebay::NICK);
        //--------------------------------

        // Join ebay_listings_table
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
                       'elo' => Mage::getResourceModel('M2ePro/Ebay_Listing_Other')->getMainTable()),
                       '(`main_table`.listing_other_id = `elo`.listing_other_id)',
                       array('item_id' => 'elo.item_id')
                   )
                   ->joinLeft(array(
                       'ea' => Mage::getResourceModel('M2ePro/Ebay_Account')->getMainTable()),
                       '(`lo`.account_id = `ea`.account_id)',
                       array('account_mode' => 'ea.mode')
                   );
        //--------------------------------

        // Set listing filter
        //--------------------------------
        if (isset($ebayListingData['id'])) {
            $collection->addFieldToFilter('`main_table`.listing_other_id', $ebayListingData['id']);
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
            'index'     => 'create_date'
        ));

        $this->addColumn('item_id', array(
            'header' => Mage::helper('M2ePro')->__('eBay Item ID'),
            'align'  => 'left',
            'width'  => '100px',
            'type'   => 'text',
            'index'  => 'item_id',
            'filter_index' => 'elo.item_id',
            'frame_callback' => array($this, 'callbackColumnItemId')
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

    public function callbackColumnItemId($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = Mage::helper('M2ePro')->__('N/A');
        } else {
            $url = Mage::helper('M2ePro/Component_Ebay')->getItemUrl($row->getData('item_id'),
                                                                     $row->getData('account_mode'),
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
        return $this->getUrl('*/adminhtml_ebay_log/listingOtherGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################
}