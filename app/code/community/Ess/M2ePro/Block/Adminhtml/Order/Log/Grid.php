<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Order_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('orderLogGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    protected function _prepareCollection()
    {
        /** @var $collection Mage_Core_Model_Mysql4_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Order_Log')->getCollection();

        $collection->getSelect()->joinLeft(
            array('mo' => Mage::getResourceModel('M2ePro/Order')->getMainTable()),
            '(mo.id = `main_table`.order_id)',
            array('magento_order_id')
        );

        $collection->getSelect()->joinLeft(
            array('so' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
            '(so.entity_id = `mo`.magento_order_id)',
            array('magento_order_number' => 'increment_id')
        );

        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $collection->addFieldToFilter('main_table.order_id', $orderId);
        }

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) == 1) {
            if (Mage::helper('M2ePro/Component_Ebay')->isActive()) {
                $collection->addFieldToFilter('main_table.component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
            }
            if (Mage::helper('M2ePro/Component_Amazon')->isActive()) {
                $collection->addFieldToFilter('main_table.component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);
            }
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '165px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $ebayTitle = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
            $amazonTitle = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);
            $options = array(
                Ess_M2ePro_Helper_Component_Ebay::NICK   => $ebayTitle,
                Ess_M2ePro_Helper_Component_Amazon::NICK => $amazonTitle
            );

            $this->addColumn('component_mode', array(
                'header'         => Mage::helper('M2ePro')->__('Channel'),
                'align'          => 'right',
                'width'          => '100px',
                'type'           => 'options',
                'index'          => 'component_mode',
                'filter_index'   => 'main_table.component_mode',
                'sortable'       => false,
                'options'        => $options
            ));
        }

        $this->addColumn('channel_order_id', array(
            'header'    => Mage::helper('M2ePro')->__('Order #'),
            'align'     => 'left',
            'width'     => '180px',
            'sortable'  => false,
            'index'     => 'channel_order_id',
            'frame_callback' => array($this, 'callbackColumnChannelOrderId'),
            'filter_condition_callback' => array($this, 'callbackFilterChannelOrderId')
        ));

        $this->addColumn('magento_order_number', array(
            'header'    => Mage::helper('M2ePro')->__('Magento Order #'),
            'align'     => 'left',
            'width'     => '150px',
            'index'     => 'so.increment_id',
            'frame_callback' => array($this, 'callbackColumnMagentoOrderNumber')
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('M2ePro')->__('Type'),
            'align'     => 'left',
            'width'     => '65px',
            'index'     => 'type',
            'type'      => 'options',
            'options'   => array(
                Ess_M2ePro_Model_Order_Log::TYPE_ERROR => Mage::helper('M2ePro')->__('Error'),
                Ess_M2ePro_Model_Order_Log::TYPE_WARNING => Mage::helper('M2ePro')->__('Warning'),
                Ess_M2ePro_Model_Order_Log::TYPE_SUCCESS => Mage::helper('M2ePro')->__('Success'),
                Ess_M2ePro_Model_Order_Log::TYPE_NOTICE => Mage::helper('M2ePro')->__('Notice'),
            ),
            'frame_callback' => array($this, 'callbackColumnType')
        ));

        $this->addColumn('message', array(
            'header'    => Mage::helper('M2ePro')->__('Description'),
            'align'     => 'left',
            'width'     => '*',
            'index'     => 'message',
            'frame_callback' => array($this, 'callbackColumnMessage')
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
        $type = $row->getData('type');

        switch ($type) {
            case Ess_M2ePro_Model_Order_Log::TYPE_SUCCESS:
                $message = "<span style=\"color: green;\">{$value}</span>";
                break;
            case Ess_M2ePro_Model_Order_Log::TYPE_NOTICE:
                $message = "<span style=\"color: blue;\">{$value}</span>";
                break;
            case Ess_M2ePro_Model_Order_Log::TYPE_WARNING:
                $message = "<span style=\"color: orange;\">{$value}</span>";
                break;
            case Ess_M2ePro_Model_Order_Log::TYPE_ERROR:
            default:
                $message = "<span style=\"color: red;\">{$value}</span>";
                break;
        }

        return $message;
    }

    public function callbackColumnChannelOrderId($value, $row, $column, $isExport)
    {
        $mode = $row->getData('component_mode');
        $order = Mage::helper('M2ePro/Component')->getComponentModel($mode, 'Order')->load($row->getData('order_id'));

        if (is_null($order->getId())) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        switch ($mode) {
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $channelOrderId = $order->getData('ebay_order_id');
                $url = $this->getUrl('*/adminhtml_ebay_order/view', array('id' => $row->getData('order_id')));
                break;
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $channelOrderId = $order->getData('amazon_order_id');
                $url = $this->getUrl('*/adminhtml_amazon_order/view', array('id' => $row->getData('order_id')));
                break;
            default:
                $channelOrderId = Mage::helper('M2ePro')->__('N/A');
                $url = '#';
        }

        return '<a href="'.$url.'" target="_blank">'.Mage::helper('M2ePro')->escapeHtml($channelOrderId).'</a>';
    }

    public function callbackColumnMagentoOrderNumber($value, $row, $column, $isExport)
    {
        $magentoOrderId = $row->getData('magento_order_id');
        $magentoOrderNumber = $row->getData('magento_order_number');

        if (!$magentoOrderId) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $url = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $magentoOrderId));

        return '<a href="'.$url.'" target="_blank">'.Mage::helper('M2ePro')->escapeHtml($magentoOrderNumber).'</a>';
    }

    public function callbackFilterChannelOrderId($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $ordersIds = array();

        if (Mage::helper('M2ePro/Component_Ebay')->isActive()) {
            $tempOrdersIds = Mage::getModel('M2ePro/Ebay_Order')
                ->getCollection()
                ->addFieldToFilter('ebay_order_id', array('like' => '%'.$value.'%'))
                ->getColumnValues('order_id');
            $ordersIds = array_merge($ordersIds, $tempOrdersIds);
        }

        if (Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            $tempOrdersIds = Mage::getModel('M2ePro/Amazon_Order')
                ->getCollection()
                ->addFieldToFilter('amazon_order_id', array('like' => '%'.$value.'%'))
                ->getColumnValues('order_id');
            $ordersIds = array_merge($ordersIds, $tempOrdersIds);
        }

        $ordersIds = array_unique($ordersIds);

        $collection->addFieldToFilter('`main_table`.order_id', array('in' => $ordersIds));
    }

    //##############################################################

    public function getRowUrl($row)
    {
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderGrid', array('_current' => true));
    }
}