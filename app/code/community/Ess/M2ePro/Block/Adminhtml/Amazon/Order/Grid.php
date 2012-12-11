<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var $itemsCollection Ess_M2ePro_Model_Mysql4_Order_Item_Collection */
    private $itemsCollection = NULL;

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonOrderGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('purchase_create_date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_component_grid_massaction';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order');

        $collection->getSelect()
                   ->joinLeft(
                       array('so' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
                       '(so.entity_id = `main_table`.magento_order_id)',
                       array('magento_order_num' => 'increment_id'));

        // Add Filter By Account
        //------------------------------
        if ($accountId = $this->getRequest()->getParam('amazonAccount')) {
            $collection->addFieldToFilter('`main_table`.account_id', $accountId);
        }
        //------------------------------

        // Add Filter By Marketplace
        //------------------------------
        if ($marketplaceId = $this->getRequest()->getParam('amazonMarketplace')) {
            $collection->addFieldToFilter('`main_table`.marketplace_id', $marketplaceId);
        }
        //------------------------------

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $this->itemsCollection = Mage::helper('M2ePro/Component_Amazon')
            ->getCollection('Order_Item')
            ->addFieldToFilter('order_id', array('in' => $this->getCollection()->getColumnValues('id')));

        return parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('purchase_create_date', array(
            'header' => Mage::helper('M2ePro')->__('Sale Date'),
            'align'  => 'left',
            'type'   => 'datetime',
            'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'  => 'purchase_create_date',
            'width'  => '170px'
        ));

        $this->addColumn('magento_order_num', array(
            'header' => Mage::helper('M2ePro')->__('Magento Order #'),
            'align'  => 'left',
            'index'  => 'so.increment_id',
            'width'  => '110px',
            'frame_callback' => array($this, 'callbackColumnMagentoOrder')
        ));

        $this->addColumn('amazon_order_id', array(
            'header' => Mage::helper('M2ePro')->__('Amazon Order #'),
            'align'  => 'left',
            'width'  => '110px',
            'index'  => 'amazon_order_id'
        ));

        $this->addColumn('amazon_order_items', array(
            'header' => Mage::helper('M2ePro')->__('Items'),
            'align'  => 'left',
            'index'  => 'amazon_order_items',
            'sortable' => false,
            'width'  => '*',
            'frame_callback' => array($this, 'callbackColumnItems'),
            'filter_condition_callback' => array($this, 'callbackFilterItems')
        ));

        $this->addColumn('buyer', array(
            'header' => Mage::helper('M2ePro')->__('Buyer'),
            'align'  => 'left',
            'index'  => 'buyer_name',
            'width'  => '120px',
            'frame_callback' => array($this, 'callbackColumnBuyer')
        ));

        $this->addColumn('paid_amount', array(
            'header' => Mage::helper('M2ePro')->__('Total Paid'),
            'align'  => 'left',
            'width'  => '110px',
            'index'  => 'paid_amount',
            'type'   => 'number',
            'frame_callback' => array($this, 'callbackColumnTotal')
        ));

        $this->addColumn('is_afn_channel', array(
            'header' => Mage::helper('M2ePro')->__('Fulfillment'),
            'width' => '100px',
            'index' => 'is_afn_channel',
            'filter_index' => 'second_table.is_afn_channel',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                0 => Mage::helper('M2ePro')->__('Merchant'),
                1 => Mage::helper('M2ePro')->__('Amazon')
            ),
            'frame_callback' => array($this, 'callbackColumnAfnChannel')
        ));

        $helper = Mage::helper('M2ePro');

        $this->addColumn('status', array(
            'header'  => Mage::helper('M2ePro')->__('Status'),
            'align'   => 'left',
            'width'   => '50px',
            'index'   => 'status',
            'filter_index' => 'second_table.status',
            'type'    => 'options',
            'options' => array(
                Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING             => $helper->__('Pending'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_UNSHIPPED           => $helper->__('Unshipped'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED_PARTIALLY   => $helper->__('Partially Shipped'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED             => $helper->__('Shipped'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_INVOICE_UNCONFIRMED => $helper->__('Invoice Not Confirmed'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_UNFULFILLABLE       => $helper->__('Unfulfillable'),
                Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED            => $helper->__('Canceled')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus')
        ));

        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_order/index', array(
            'tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_AMAZON
        ));

        $this->addColumn('action', array(
            'header'  => Mage::helper('M2ePro')->__('Action'),
            'width'   => '80px',
            'type'    => 'action',
            'getter'  => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('M2ePro')->__('View'),
                    'url'     => array('base' => '*/adminhtml_amazon_order/view'),
                    'field'   => 'id'
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Edit Shipping Address'),
                    'url'     => array('base' => '*/adminhtml_amazon_order/editShippingAddress/back/'.$back.'/'),
                    'field'   => 'id'
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Create Order'),
                    'url'     => array('base' => '*/adminhtml_amazon_order/createMagentoOrder'),
                    'field'   => 'id'
                ),
                array(
                    'caption' => Mage::helper('M2ePro')->__('Mark As Shipped'),
                    'url'     => array('base' => '*/adminhtml_amazon_order/updateShippingStatus'),
                    'field'   => 'id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'is_system' => true
        ));

        return parent::_prepareColumns();
    }

    //##############################################################

    public function callbackColumnMagentoOrder($value, $row, $column, $isExport)
    {
        $magentoOrderId = $row['magento_order_id'];
        $returnString = Mage::helper('M2ePro')->__('N/A');

        if ($magentoOrderId !== '' && $magentoOrderId !== null) {

            if ($row['magento_order_num']) {
                $orderUrl = Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view',
                                                              array('order_id' => $magentoOrderId),
                                                              null);
                $returnString = '<a href="' . $orderUrl . '" target="_blank">' . $row['magento_order_num'] . '</a>';
            } else {
                $returnString = '<span style="color: red;" title="'
                                .Mage::helper('M2ePro')->__('Deleted')
                                .'">'.$magentoOrderId.'</span>';
            }

        }

        return $returnString.$this->getViewLogIconHtml($row->getId());
    }

    private function getViewLogIconHtml($orderId)
    {
        // Prepare collection
        // --------------------------------
        $orderLogsCollection = Mage::getModel('M2ePro/Order_Log')->getCollection()
            ->addFieldToFilter('order_id', (int)$orderId)
            ->setOrder('id', 'DESC');
        $orderLogsCollection->getSelect()
            ->limit(3);
        // --------------------------------

        // Prepare logs data
        // --------------------------------
        if ($orderLogsCollection->count() <= 0) {
            return '';
        }

        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $logRows = array();
        foreach ($orderLogsCollection as $log) {
            $logRows[] = array(
                'type' => $log->getData('type'),
                'text' => Mage::getSingleton('M2ePro/Log_Abstract')->decodeDescription($log->getData('message')),
                'date' => Mage::app()->getLocale()->date(strtotime($log->getData('create_date')))->toString($format)
            );
        }

        $lastLogRow = $logRows[0];
        // --------------------------------

        // Get log icon
        // --------------------------------
        $icon = 'normal';
        $iconTip = Mage::helper('M2ePro')->__('Last order action was completed successfully.');

        if ($lastLogRow['type'] == Ess_M2ePro_Model_Order_Log::TYPE_ERROR) {
            $icon = 'error';
            $iconTip = Mage::helper('M2ePro')->__('Last order action was completed with error(s).');
        } else if ($lastLogRow['type'] == Ess_M2ePro_Model_Order_Log::TYPE_WARNING) {
            $icon = 'warning';
            $iconTip = Mage::helper('M2ePro')->__('Last order action was completed with warning(s).');
        }

        $iconSrc = $this->getSkinUrl('M2ePro').'/images/log_statuses/'.$icon.'.png';
        // --------------------------------

        $html = '<span style="float:right;">';
        $html .= '<a title="'.$iconTip.'" id="orders_grid_help_icon_open_'
                 .(int)$orderId
                 .'" href="javascript:void(0);" onclick="OrderHandlerObj.viewOrderHelp('
                 .(int)$orderId.',\''.base64_encode(json_encode($logRows)).'\', \''
                 .$this->getId().'\');"><img src="'.$iconSrc.'" /></a>';
        $html .= '<a title="'.$iconTip
                 .'" id="orders_grid_help_icon_close_'
                 .(int)$orderId
                 .'" style="display:none;" href="javascript:void(0);" onclick="OrderHandlerObj.hideOrderHelp('
                 .(int)$orderId.', \''.$this->getId().'\');"><img src="'.$iconSrc.'" /></a>';
        $html .= '</span>';

        return $html;
    }

    //--------------------------------------------------------------

    public function callbackColumnItems($value, $row, $column, $isExport)
    {
        $items = $this->itemsCollection->getItemsByColumnValue('order_id', $row->getData('id'));

        $html = '';

        foreach ($items as $item) {
            if ($html != '') {
                $html .= '<br />';
            }

            $skuHtml = '';
            if ($item->getSku()) {
                $skuLabel = Mage::helper('M2ePro')->__('SKU');
                $sku = Mage::helper('M2ePro')->escapeHtml($item->getSku());

                $skuHtml = <<<STRING
<span style="padding-left: 10px;">
    <b>{$skuLabel}:</b> {$sku}
</span><br />
STRING;
            }

            $generalIdLabel = Mage::helper('M2ePro')->__($item->getIsIsbnGeneralId() ? 'ISBN' : 'ASIN');
            $generalId = Mage::helper('M2ePro')->escapeHtml($item->getGeneralId());

            $itemUrl = Mage::helper('M2ePro/Component_Amazon')->getItemUrl($item->getGeneralId(),
                                                                           $row->getData('marketplace_id'));

            $generalIdHtml = <<<STRING
<span style="padding-left: 10px;">
    <b>{$generalIdLabel}:</b>&nbsp;<a href="{$itemUrl}" target="_blank">{$generalId}</a>
</span><br />
STRING;

            $itemTitle = Mage::helper('M2ePro')->escapeHtml($item->getTitle());
            $qtyLabel = Mage::helper('M2ePro')->__('QTY');

            $html .= <<<HTML
{$itemTitle}<br />
<small>
    {$generalIdHtml}
    {$skuHtml}
    <span style="padding-left: 10px;">
        <b>{$qtyLabel}:</b> {$item->getQtyPurchased()}
    </span>
</small>
HTML;
        }

        return $html;
    }

    public function callbackColumnBuyer($value, $row, $column, $isExport)
    {
        if ($row->getData('buyer_name') == '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $html = Mage::helper('M2ePro')->escapeHtml($row->getData('buyer_name'));

        if ($row->getData('buyer_email') != '') {
            $html .= '<br />';
            $html .= '<' . Mage::helper('M2ePro')->escapeHtml($row->getData('buyer_email')) . '>';
        }

        return $html;
    }

    public function callbackColumnTotal($value, $row, $column, $isExport)
    {
        return Mage::getSingleton('M2ePro/Currency')->formatPrice(
            $row->getData('currency'), $row->getData('paid_amount')
        );
    }

    public function callbackColumnAfnChannel($value, $row, $column, $isExport)
    {
        switch ($row->getData('is_afn_channel')) {
            case Ess_M2ePro_Model_Amazon_Listing_Product::IS_ISBN_GENERAL_ID_YES:
                $value = '<span style="font-weight: bold;">' . $value . '</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $status = $row->getData('status');

        $statusColors = array(
            Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING  => 'gray',
            Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED  => 'green',
            Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED => 'red'
        );

        $color = isset($statusColors[$status]) ? $statusColors[$status] : 'black';
        $value = '<span style="color: '.$color.';">'.$value.'</span>';

        if ($row->isLockedObject('update_order_status')) {
            $value .= '<br />';
            $value .= '<span style="color: gray;">['
                      .Mage::helper('M2ePro')->__('Status Update in Progress...').']</span>';
        }

        return $value;
    }

    //##############################################################

    protected function callbackFilterItems($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $orderItemsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order_Item');

        $orderItemsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $orderItemsCollection->getSelect()->columns('order_id');
        $orderItemsCollection->getSelect()->distinct(true);

        $orderItemsCollection->getSelect()->where('title LIKE ? OR sku LIKE ? or general_id LIKE ?', '%'.$value.'%');

        $totalResult = $orderItemsCollection->getColumnValues('order_id');
        $collection->addFieldToFilter('`main_table`.id', array('in' => $totalResult));
    }

    //##############################################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_order/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_order/index', array(
            'tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_AMAZON
        ));

        return $this->getUrl('*/adminhtml_amazon_order/view', array('id' => $row->getId(), 'back' => $back));
    }

    //##############################################################
}