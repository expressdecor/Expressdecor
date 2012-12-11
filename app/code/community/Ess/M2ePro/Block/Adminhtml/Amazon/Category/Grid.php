<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('categoryGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //------------------------------
    }

    protected function _prepareCollection()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');

        $collection = Mage::getModel('M2ePro/Amazon_Category')->getCollection();
        $collection->addFieldToFilter('`marketplace_id`', $marketplaceId);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'       => Mage::helper('M2ePro')->__('ID'),
            'align'        => 'right',
            'type'         => 'number',
            'width'        => '50px',
            'index'        => 'id',
            'filter_index' => 'id',
            'frame_callback' => array($this, 'callbackColumnId')
        ));

        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Title'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '150px',
            'index'        => 'title',
            'filter_index' => 'title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('node_title', array(
            'header'       => Mage::helper('M2ePro')->__('Node Title'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '100px',
            'index'        => 'node_title',
            'filter_index' => 'node_title',
            'frame_callback' => array($this, 'callbackColumnNodeTitle')
        ));

        $this->addColumn('category_path', array(
            'header'       => Mage::helper('M2ePro')->__('Category Path'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '350px',
            'index'        => 'category_path',
            'filter_index' => 'category_path',
            'frame_callback' => array($this, 'callbackColumnCategoryPath')
        ));

//        $this->addColumn('select', array(
//            'header'       => Mage::helper('M2ePro')->__('Select'),
//            'align'        => 'center',
//            'type'         => 'text',
//            'width'        => '100px',
//            'filter'    => false,
//            'sortable'  => false,
//            'frame_callback' => array($this, 'callbackColumnSelectCategory')
//        ));

        $marketplace_id = $this->getRequest()->getParam('marketplace_id');
        $listing_product_ids = $this->getRequest()->getParam('listing_product_ids');

        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_amazon_category',array(
            'marketplace_id'      => $marketplace_id,
            'listing_product_ids' => $listing_product_ids
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Select This Category'),
                    'url'       => array('base'=>
                                           '*/adminhtml_amazon_category/map/listing_product_ids/'.$listing_product_ids),
                    'field'     => 'id',
                    'confirm'   => Mage::helper('M2ePro')->__('Are you sure?')
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit Category'),
                    'url'       => array('base'=> '*/adminhtml_amazon_category/edit/marketplace_id/'
                                                  .$marketplace_id.
                                                  '/listing_product_ids/'
                                                  .$listing_product_ids.
                                                  '/back/'.$back),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Delete Category'),
                    'url'       => array('base'=> '*/adminhtml_amazon_category/delete/marketplace_id/'
                                                  .$marketplace_id.
                                                  '/listing_product_ids/'
                                                  .$listing_product_ids.
                                                  '/back/'.$this->getRequest()->getParam('back')),
                    'field'     => 'id',
                    'confirm'   => Mage::helper('M2ePro')->__('Are you sure?')
                ),
            )
        ));
    }

    // ####################################

    public function callbackColumnId($value, $row, $column, $isExport)
    {
        return $value.'&nbsp;';
    }

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        return '&nbsp'.$value;
    }

    public function callbackColumnNodeTitle($value, $row, $column, $isExport)
    {
        return '&nbsp'.$value;
    }

    public function callbackColumnCategoryPath($value, $row, $column, $isExport)
    {
        return '&nbsp;'.$value;
    }

    public function callbackColumnSelectCategory($value, $row, $column, $isExport)
    {
        $url = $this->getUrl('*/adminhtml_amazon_category/map/', array('id' => $row->getId(),
                                                                       'listing_product_id' =>
                                                                            $this->getRequest()
                                                                                ->getParam('listing_product_id')));

        return '<a href="'.$url.'">Select This Category</a>';
    }

    // ####################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    $$('#listingMoveToListingGrid div.grid th').each(function(el){
        el.style.padding = '2px 4px';
    });

    $$('#listingMoveToListingGrid div.grid td').each(function(el){
        el.style.padding = '2px 4px';
    });

</script>
JAVASCRIPT;

        return parent::_toHtml() . $javascriptsMain;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_category/categoryGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        $marketplace_id = $this->getRequest()->getParam('marketplace_id');
        $listing_product_ids = $this->getRequest()->getParam('listing_product_ids');

        $back = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_amazon_category',array(
            'marketplace_id'      => $marketplace_id,
            'listing_product_ids' => $listing_product_ids
        ));

        return $this->getUrl('*/adminhtml_amazon_category/edit', array(
            'id' => $row->getId(),
            'marketplace_id' => $marketplace_id,
            'listing_product_ids' => $listing_product_ids,
            'back' => $back
        ));
    }

    // ####################################
}