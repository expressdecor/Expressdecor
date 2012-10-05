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
 * @package    AW_Followupemail
 * @version    3.4.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AWW_Followupemail_Block_Adminhtml_Coupons_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('FUE_Coupons')
            ->setDefaultSort('expiration_date')
            ->setDefaultDir('DESC')
            ->setSaveParametersInSession(true)
            ->setUseAjax(false);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('salesrule/coupon')->getCollection();
        $collection->getSelect()->joinLeft(
            array('scr' => $collection->getTable('salesrule/rule')),
            'main_table.rule_id = scr.rule_id',
            array('coupon_type'))
            ->where('scr.coupon_type = ?', Mage::helper('followupemail/coupon')->getFUECouponsCode());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'index' => 'code',
            'header' => $this->__('Coupon Code')
        ));

        $this->addColumn('times_used', array(
            'index' => 'times_used',
            'header' => $this->__('Times Used'),
            'width' => '150px',
            'filter_condition_callback' => array($this, '_filterByTimesUsed')
        ));

        $this->addColumn('expiration_date', array(
            'index' => 'expiration_date',
            'header' => $this->__('Expiration Date'),
            'type' => 'date',
            'width' => '200px'
        ));

        $this->addColumn('action', array(
            'header' => $this->__('Actions'),
            'width' => '150px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $this->__('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                    'confirm' => $this->__('Are you sure you want do this?')
                )
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true
        ));
    }

    protected function _filterByTimesUsed($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) return $this;
        $collection->getSelect()->where('main_table.times_used = ?', $value);
        return $this;
    }
}
