<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Category extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonCategory');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_category';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__("TODO TEXT Categories For %name% Marketplace");
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (!is_null($this->getRequest()->getParam('back'))) {

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\''.Mage::helper('M2ePro')
                    ->getBackUrl('*/adminhtml_amazon_listing/view',
                                 array('id' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_AMAZON)).'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $marketplace_id      = $this->getRequest()->getParam('marketplace_id');
        $listing_product_ids = $this->getRequest()->getParam('listing_product_ids');

        $tempUrl = $this->getUrl('*/adminhtml_amazon_category/add',array(
            'marketplace_id' => $marketplace_id,
            'listing_product_ids' => $listing_product_ids,
            'back' => Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_amazon_category',array(
                'marketplace_id'      => $marketplace_id,
                'listing_product_ids' => $listing_product_ids
            ))
        ));

        $this->_addButton('new', array(
            'label'     => Mage::helper('M2ePro')->__('Add Category'),
            'onclick'   => 'setLocation(\''.$tempUrl.'\')',
            'class'     => 'add'
        ));
        //------------------------------
    }

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_category_help');

        return $helpBlock->toHtml() . parent::getGridHtml();
    }
}