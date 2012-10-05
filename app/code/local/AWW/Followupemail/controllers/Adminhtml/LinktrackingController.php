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


class AWW_Followupemail_Adminhtml_LinktrackingController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        return $this->loadLayout()->_setActiveMenu('followupemail/items');
    }

    public function indexAction()
    {
        if (Mage::helper('followupemail')->checkVersion('1.4'))
            $this->_title("Follow Up Email Link Tracking");
        if($this->getRequest()->getQuery('ajax'))
        {
            $this->_forward('grid');
            return;
        }
        $this->_initAction()->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('followupemail/adminhtml_linktracking_grid')->toHtml());
    }

    public function viewCustomerAction()
    {
        $track = Mage::getModel('followupemail/linktracking')->load($this->getRequest()->getParam('id'));
        $queue = Mage::getModel('followupemail/queue')->load($track->getQueueId());
        $email = $queue->getRecipientEmail();

        if(!$id = Mage::getModel('followupemail/mysql4_linktracking')->getCustomerByEmail($email))
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('followupemail')
                        ->__('There is no registered customer with ID or email ').$email);
            $this->_redirectReferer();
        }

        $this->_redirect('adminhtml/customer/edit', array('id' => $id));
    }

    public function viewCartAction()
    {
        $track = Mage::getModel('followupemail/linktracking')->load($this->getRequest()->getParam('id'));
        $queue = Mage::getModel('followupemail/queue')->load($track->getQueueId());
        $email = $queue->getRecipientEmail();

        if(!$id = Mage::getModel('followupemail/mysql4_linktracking')->getCustomerByEmail($email))
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('followupemail')
                    ->__('The owner of this cart is not a registered customer'));
            $this->_redirectReferer();
        }
        $this->_redirect('adminhtml/customer/edit', array('id' => $id, 'active_tab' => 'cart'));
    }

    public function viewOrderAction()
    {
        $id = Mage::getResourceModel('followupemail/linktracking_collection')
                ->getLinktrackingData(
                    'order_entity_id',
                    $this->getRequest()->getParam('id'), 
                    AWW_Followupemail_Model_Source_Linktracking_Types::LINKTRACKING_TYPE_LINK_CART_ORDER);

        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $id, 'active_tab' => 'cart'));
    }

    public function viewEmailAction()
    {
        $track = Mage::getModel('followupemail/linktracking')->load($this->getRequest()->getParam('id'));
        $this->_redirect('*/adminhtml_queue/preview', array('id' => $track->getQueueId()));
    }

    public function viewRuleAction()
    {
        $track = Mage::getModel('followupemail/linktracking')->load($this->getRequest()->getParam('id'));
        $queue = Mage::getModel('followupemail/queue')->load($track->getQueueId());
        $this->_redirect('*/adminhtml_rules/edit', array('id' => $queue->getRuleId()));
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('followupemail/linktracking');
    }
}
