<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AWW_Followupemail
 * @version    3.4.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AWW_Followupemail_Block_Adminhtml_Linktracking_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('FUE_LinkTrackingGrid')
            ->setDefaultSort('visited_at')
            ->setDefaultDir('DESC')
            ->setSaveParametersInSession(true)
            ->setUseAjax(false);
    }

    protected function _toHtml()
    {
        return $this->getLayout()->createBlock('followupemail/adminhtml_linktracking_queryselector')
                    ->toHtml()
                .parent::_toHtml();
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('followupemail/linktracking_collection')
                        ->getLinktrackingCollection(AWW_Followupemail_Helper_Data::getLinktrackingQueryType());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('email_created_at',
            array(
                'index'  => 'email_created_at',
                'type'   => 'datetime',
                'header' => $this->__('Created at'),
                'align'  => 'left',
                'width'  => '145px',
                'filter_condition_callback' => array($this, '_filterConditionDate'),
            )
        );

        $this->addColumn('email_sent_at',
            array(
                'index'  => 'email_sent_at',
                'type'   => 'datetime',
                'header' => $this->__('Sent at'),
                'align'  => 'left',
                'width'  => '145px',
                'filter_condition_callback' => array($this, '_filterConditionDate'),
            )
        );

        $this->addColumn('link_visited_at',
            array(
                'index'   => 'link_visited_at',
                'type'    => 'datetime',
                'header'  => $this->__('Link visited at'),
                'align'   => 'left',
                'width'   => '145px',
                'filter_condition_callback' => array($this, '_filterConditionDate'),
            )
        );

        $this->addColumn('email_sequence_number',
            array(
                'index'  => 'email_sequence_number',
                'header' => $this->__('Seq. No'),
                'align'  => 'center',
                'width'  => '50px',
                'filter_condition_callback' => array($this, '_filterCondition'),
            )
        );

        $this->addColumn('rule_title',
            array(
                'index'  => 'rule_title',
                'header' => $this->__('Rule'),
                'align'  => 'left',
                'filter_condition_callback' => array($this, '_filterConditionText'),
            )
        );

        $this->addColumn('rule_event_type',
            array(
                'index'  => 'rule_event_type',
                'header' => $this->__('Event'),
                'align'  => 'left',
                'type'    => 'options',
                'options' => Mage::getModel('followupemail/source_rule_types')->toShortOptionArray(),
                'filter_condition_callback' => array($this, '_filterCondition'),
            )
        );

        $this->addColumn('email_recipient_name',
            array(
                'index'  => 'email_recipient_name',
                'header' => $this->__('Recipient name'),
                'align'  => 'left',
                'filter_condition_callback' => array($this, '_filterConditionText'),
            )
        );

        $this->addColumn('email_recipient_email',
            array(
                'index'  => 'email_recipient_email',
                'header' => $this->__('Recipient email'),
                'align'  => 'left',
                'filter_condition_callback' => array($this, '_filterConditionText'),
            )
        );

// adding cart-related fields
        if(AWW_Followupemail_Model_Source_Linktracking_Types::LINKTRACKING_TYPE_LINK_CART == $this->_queryType)
        {
            $this->addColumn('quote_created_at',
                array(
                    'index'  => 'quote_created_at',
                    'type'   => 'datetime',
                    'header' => $this->__('Quote created at'),
                    'align'  => 'left',
                    'filter_condition_callback' => array($this, '_filterConditionDate'),
                )
            );

            $this->addColumn('quote_grand_total',
                array(
                    'index'  => 'quote_grand_total',
                    'type'   => 'currency',
                    'header' => $this->__('Quote grand total'),
                    'align'  => 'left',
                    'filter_condition_callback' => array($this, '_filterConditionCurrency'),
                )
            );
        }


// adding order-related fields
        if(AWW_Followupemail_Model_Source_Linktracking_Types::LINKTRACKING_TYPE_LINK_CART_ORDER == $this->_queryType)
        {
            $this->addColumn('quote_created_at',
                array(
                    'index'  => 'quote_created_at',
                    'type'   => 'datetime',
                    'header' => $this->__('Quote created at'),
                    'align'  => 'left',
                    'filter_condition_callback' => array($this, '_filterConditionDate'),
                )
            );

            $this->addColumn('order_increment_id',
                array(
                    'index'  => 'order_increment_id',
                    'header' => $this->__('Order No'),
                    'align'  => 'left',
                    'filter_condition_callback' => array($this, '_filterConditionText'),
                )
            );

            if(AWW_Followupemail_Helper_Data::getMagentoVersionCode() == AWW_Followupemail_Helper_Data::MAGENTO_VERSION_CE_1_3)
                $this->addColumn('order_is_active',
                    array(
                        'index'  => 'order_is_active',
                        'type'   => 'options',
                        'header' => $this->__('Order is active'),
                        'align'  => 'left',
                        'options' => array( 1 => $this->__('Yes'),
                                            0 => $this->__('No'),
                                        ),
                        'filter_condition_callback' => array($this, '_filterCondition'),
                    )
                );

            $this->addColumn('order_grand_total',
                array(
                    'index'  => 'order_grand_total',
                    'type'   => 'currency',
                    'header' => $this->__('Order Grand Total'),
                    'align'  => 'left',
                    'filter_condition_callback' => array($this, '_filterConditionCurrency'),
                )
            );
        }

// actions
        $actions = array(
            array(
                'caption'   => $this->__('View email'),
                'url'       => array('base' => '*/*/viewEmail'),
                'field'     => 'id',
                'popup'     => true,
            ),
            array(
                'caption'   => $this->__('View rule'),
                'url'       => array('base' => '*/*/viewRule'),
                'field'     => 'id',
                'popup'     => true,
            ),
            array(
                'caption'   => $this->__('View customer (if registered)'),
                'url'       => array('base' => '*/*/viewCustomer'),
                'field'     => 'id',
                'popup'     => true,
            ),
        );

        if(false !== strpos($this->_queryType, 'cart'))
            $actions[] = array(
                            'caption'   => $this->__('View cart (if customer is registered)'),
                            'url'       => array('base' => '*/*/viewCart'),
                            'field'     => 'id',
                            'popup'     => true,
                        );

        if(false !== strpos($this->_queryType, 'order'))
            $actions[] = array(
                            'caption'   => $this->__('View order'),
                            'url'       => array('base' => '*/*/viewOrder'),
                            'field'     => 'id',
                            'popup'     => true,
                        );

        $this->addColumn('action', array(
                'header'    =>  $this->__('Action'),
                'width'     => '120',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => $actions,
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/viewEmail', array('id' => $row->getId()));
    }


// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = service
    protected function getRealFieldName($name)
    {
        $res = AWW_Followupemail_Model_Mysql4_Linktracking_Collection::getRealFieldName($name);
        if(!$res)
        {
            $parts = explode('_', $name, 2);
            if(count($parts)<2) $res = $name;
            else $res = substr($parts[0], 0, 1).'.'.$parts[1];
        }
        return $res;
    }

    protected function _filterConditionText($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) return;
        $collection->getSelect()->where($this->getRealFieldName($column->getIndex())." LIKE '%$value%'", $value);
    }

    protected function _filterConditionCurrency($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) return;

        if(isset($value['from']) && isset($value['to']))
            $collection->getSelect()->where($this->getRealFieldName($column->getIndex()).' BETWEEN \''.
                addslashes($value['from']).'\' AND \''.addslashes($value['to']).'\'');
        elseif(isset($value['from']))
            $collection->getSelect()->where($this->getRealFieldName($column->getIndex()).'>\''.addslashes($value['from']).'\'');
        elseif(isset($value['to']))
            $collection->getSelect()->where($this->getRealFieldName($column->getIndex()).'<\''.addslashes($value['to']).'\'');
    }

    protected function _filterConditionDate($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) return;

        if(isset($value['from']) && isset($value['to']))
            $collection->getSelect()->where($this->getRealFieldName($column->getIndex()).' BETWEEN \''.
                date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $value['from']->get(Zend_Date::TIMESTAMP))
                .'\' AND \''.
                date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $value['to']->get(Zend_Date::TIMESTAMP))
                .'\'');
        elseif(isset($value['from']))
            $collection->getSelect()->where($this->getRealFieldName($column->getIndex()).'>\''.date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $value['from']->get(Zend_Date::TIMESTAMP)).'\'');
        elseif(isset($value['to']))
            $collection->getSelect()->where($this->getRealFieldName($column->getIndex()).'<\''.date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, $value['to']->get(Zend_Date::TIMESTAMP)).'\'');
    }

    protected function _filterCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) return;
        $collection->getSelect()->where($this->getRealFieldName($column->getIndex()).'="'.mysql_escape_string($value).'"');
    }

}