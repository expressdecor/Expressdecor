<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_View_Log extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonOrderViewLog');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setFilterVisibility(false);
        $this->setUseAjax(true);
        //------------------------------

        /** @var $order Ess_M2ePro_Model_Order */
        $this->order = Mage::helper('M2ePro')->getGlobalValue('temp_data');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('M2ePro/Order_Log')->getCollection();
        $collection->addFieldToFilter('order_id', $this->order->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('M2ePro')->__('Message'),
            'align'     => 'left',
            'width'     => '*',
            'type'      => 'text',
            'sortable'  => false,
            'filter_index' => 'id',
            'index'     => 'message',
            'frame_callback' => array($this, 'callbackColumnMessage')
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('M2ePro')->__('Type'),
            'align'     => 'left',
            'width'     => '65px',
            'index'     => 'type',
            'frame_callback' => array($this, 'callbackColumnType')
        ));

        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Create Date'),
            'align'     => 'left',
            'width'     => '165px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date'
        ));

        return parent::_prepareColumns();
    }

    //##############################################################

    public function callbackColumnMessage($value, $row, $column, $isExport)
    {
        return Mage::getSingleton('M2ePro/Log_Abstract')->decodeDescription($row->getData('message'));
    }

    public function callbackColumnType($value, $row, $column, $isExport)
    {
        switch ($value) {
            case Ess_M2ePro_Model_Order_Log::TYPE_SUCCESS:
                $message = '<span style="color: green;">'.Mage::helper('M2ePro')->__('Success').'</span>';
                break;
            case Ess_M2ePro_Model_Order_Log::TYPE_NOTICE:
                $message = '<span style="color: blue;">'.Mage::helper('M2ePro')->__('Notice').'</span>';
                break;
            case Ess_M2ePro_Model_Order_Log::TYPE_WARNING:
                $message = '<span style="color: orange;">'.Mage::helper('M2ePro')->__('Warning').'</span>';
                break;
            case Ess_M2ePro_Model_Order_Log::TYPE_ERROR:
            default:
                $message = '<span style="color: red;">'.Mage::helper('M2ePro')->__('Error').'</span>';
                break;
        }

        return $message;
    }

    //##############################################################

    public function getRowUrl($row)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderLogGrid', array('_current' => true));
    }
}