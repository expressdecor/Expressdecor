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


class AWW_Followupemail_Block_Adminhtml_Queue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('FUE_QueueGrid')
            ->setDefaultSort('send_at')
            ->setDefaultDir('DESC')
            ->setDefaultFilter(array('status' => 'R'))
            ->setSaveParametersInSession(true)
            ->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('followupemail/queue')->getCollection();

        $collection->getSelect()
            ->joinLeft(array('r' => $collection->getTable('followupemail/rule')),
                        'main_table.rule_id=r.id',
                        array('title', 'event_type'));
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('followupemail');

        $this->addColumn('id', array(
            'header' => $this->__('ID'),
            'index' => 'id',
            'width' => '100px',
            'filter_condition_callback' => array($this, '_filterIdCondition')
        ));
        
        $this->addColumn('status',
            array(
                'header'  => $helper->__('Status'),
                'align'   => 'left',
                'width'   => '100px',
                'index'   => 'status',
                'type'    => 'options',
                'options' => Mage::getModel('followupemail/source_queue_status')->toOptionArray()
            )
        );

        $this->addColumn('created_at',
            array(
                'header' => $helper->__('Created at'),
                'align'  => 'left',
                'width'  => '145px',
                'type'   => 'datetime',
                'index'  => 'created_at'
            )
        );

        $this->addColumn('scheduled_at',
            array(
                'header' => $helper->__('Scheduled at'),
                'align'  => 'left',
                'width'  => '145px',
                'type'   => 'datetime',
                'index'  => 'scheduled_at',
            )
        );

        $this->addColumn('sent_at',
            array(
                'header' => $helper->__('Sent at'),
                'align'  => 'left',
                'width'  => '145px',
                'type'   => 'datetime',
                'index'  => 'sent_at',
                'empty_text' => $helper->__('Not sent yet'),
                'renderer' => 'AWW_Followupemail_Block_Adminhtml_Queue_Grid_Column_Emptydate',
            )
        );

        $this->addColumn('sequence_number',
            array(
                'header' => $helper->__('Seq. No'),
                'align'  => 'center',
                'width'  => '50px',
                'index'  => 'sequence_number',
            )
        );

        $this->addColumn('rule_title',
            array(
                'header' => $helper->__('Rule'),
                'align'  => 'left',
                'index'  => 'title',
            )
        );

        $this->addColumn('event_type',
            array(
                'header' => $helper->__('Event'),
                'align'  => 'left',
                'index'  => 'event_type',
                'type'    => 'options',
                'options' => Mage::getModel('followupemail/source_rule_types')->toShortOptionArray()
            )
        );

        $this->addColumn('recipient_name',
            array(
                'header' => $helper->__('Recipient name'),
                'align'  => 'left',
                'index'  => 'recipient_name',
            )
        );

        $this->addColumn('recipient_email',
            array(
                'header' => $helper->__('Recipient email'),
                'align'  => 'left',
                'index'  => 'recipient_email',
            )
        );

        $this->addColumn('action',
            array(
                'header'    =>  $helper->__('Action'),
                'width'     => '80',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => $helper->__('Preview'),
                        'url'       => array('base' => '*/*/preview'),
                        'field'     => 'id',
                        'popup'     => true,
                    ),
                    array(
                        'caption'   => $helper->__('Cancel'),
                        'url'       => array('base' => '*/*/cancel'),
                        'field'     => 'id',
                        'confirm'   => $helper->__('Set the email status to \'Cancelled\' ?'),
                    ),
                    array(
                        'caption'   => $helper->__('Delete'),
                        'url'       => array('base' => '*/*/delete'),
                        'field'     => 'id',
                        'confirm'   => $helper->__('Are you sure you want to delete the email ?'),
                    ),
                    array(
                        'caption'   => $helper->__('Send now'),
                        'url'       => array('base' => '*/*/send'),
                        'field'     => 'id',
                        'confirm'   => $helper->__('Are you sure you want to send the email immediately ?'),
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/preview', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid');
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('emails');
        $this->getMassactionBlock()->addItem('sendnow', array(
                'label'=> $this->__('Send now'),
                'url'  => $this->getUrl('*/*/massactionsend', array('_current'=>true)),
                'confirm'  => Mage::helper('followupemail')->__('Are you sure you want to do this?')
            )
        );
        $this->getMassactionBlock()->addItem('cancel', array(
                'label'=> $this->__('Cancel'),
                'url'  => $this->getUrl('*/*/massactioncancel', array('_current'=>true)),
                'confirm'  => Mage::helper('followupemail')->__('Are you sure you want to do this?')
            )
        );
        $this->getMassactionBlock()->addItem('delete', array(
                'label'=> $this->__('Delete'),
                'url'  => $this->getUrl('*/*/massactiondelete', array('_current'=>true)),
                'confirm'  => Mage::helper('followupemail')->__('Are you sure you want to do this?')
            )
        );
        return $this;
    }

    protected function _filterIdCondition($collection, $column) {
        if(!$value = $column->getFilter()->getValue()) return;
        $collection->getSelect()->where('main_table.id = ?', $value);
    }
}
