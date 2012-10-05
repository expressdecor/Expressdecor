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


class AWW_Followupemail_Block_Adminhtml_Rule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs 
{
    public function __construct() {
        parent::__construct();
        $this->setId('followupemail_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Rule Information'));
    }
    
    protected function _beforeToHtml() {
        $this->addTab('general', array(
                'label'   => $this->__('General'),
                'title'   => $this->__('General'),
                'content' => $this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_general')->toHtml()
            )
        );

        $this->addTab('details', array(
                'label'   => $this->__('Stores & Product Types'),
                'title'   => $this->__('Stores & Product Types'),
                'content' => $this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_details')->toHtml()
            )
        );

        $this->addTab('categories', array(
            'label'     => $this->__('Excluded Categories'),
            'url'       => $this->getUrl('*/*/categories', array('_current' => true)),
            'class'     => 'ajax',
        ));

        $this->addTab('subscribers', array(
                'label'     => $this->__('Newsletter Subscribers'),
                'title'   => $this->__('Newsletter Subscribers'),
                'content' => $this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_subscribers')->toHtml(),
            ));

        $this->addTab('sendcopy', array(
                'label'     => $this->__('Send Copy'),
                'title'   => $this->__('Send Copy'),
                'content' => $this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_sendcopy')->toHtml(),
            ));

        $this->addTab('sender', array(
                'label'     => $this->__('Sender Details'),
                'title'   => $this->__('Sender Details'),
                'content' => $this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_sender')->toHtml(),
            ));

        if(Mage::helper('followupemail/coupon')->canUseCoupons()) {
            $this->addTab('coupons', array(
                'label' => $this->__('Coupons'),
                'title' => $this->__('Coupons'),
                'content' => $this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_coupons')->toHtml()
            ));
        }

        $this->addTab('sendtest', array(
                'label'     => $this->__('Send Test Email'),
                'title'   => $this->__('Send Test Email'),
                'content' => $this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_sendtest')->toHtml(),
            ));

        $this->addTab('mss', array(
            'label'     => $this->__('Market Segmentation Suite'),
            'title'     => $this->__('Market Segmentation Suite'),
            'content'   => $this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_mss')->toHtml(),
        ));

        $this->addTab('ga', array(
            'label'     => $this->__('Google Analytics'),
            'title'     => $this->__('Google Analytics'),
            'content'   => $this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_ga')->toHtml(),
        ));
        if($tabName = $this->getRequest()->getParam('tab'))
        {
            $tabName = (strpos($tabName, 'followupemail_tabs_')!==false)
                        ? substr($tabName, strlen('followupemail_tabs_'))
                        : $tabName.'_section';

            if(isset($this->_tabs[$tabName])) $this->setActiveTab($tabName);
        }

        return parent::_beforeToHtml();
    }
}