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


class AWW_Followupemail_Adminhtml_QueueController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        return $this->loadLayout()->_setActiveMenu('followupemail/items');
    }

    public function indexAction()
    {
        if (Mage::helper('followupemail')->checkVersion('1.4'))
            $this->_title("Follow Up Email Queue");
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
        $this->getResponse()->setBody($this->getLayout()->createBlock('followupemail/adminhtml_queue_grid')->toHtml());
    }

    private function deleteEmail($id, $showMessage = true)
    {
        Mage::getModel('followupemail/queue')->setId($id)->delete();
        Mage::getSingleton('followupemail/log')->logSuccess("email id=$id deleted by Administrator", $this);
        if($showMessage) {
            $message = $this->__('Email was successfully deleted');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        }
    }

    private function cancelEmail($id, $showMessage = true)
    {
        Mage::getModel('followupemail/queue')->load($id)->cancel();
        Mage::getSingleton('followupemail/log')->logSuccess("email id=$id cancelled by Administrator", $this);
        if($showMessage) {
            $message = $this->__('Email was successfully cancelled');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        }
    }

    private function sendEmail($id, $showMessage = true) {
        $result = Mage::getModel('followupemail/queue')->load($id)->send();
        if($result === TRUE) {
            Mage::getSingleton('followupemail/log')->logSuccess("email id=$id sent by Administrator", $this);
            if($showMessage) {
                $message = $this->__('Email was successfully sent');
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
            }
        }
        else {
            Mage::getSingleton('followupemail/log')->logError("email id=$id could not be sent by Administrator", $this);
            if($showMessage) {
                $message = $this->__('Could not send the email');
                Mage::getSingleton('adminhtml/session')->addError($message);
            }
        }
    }

    public function sendResponse($result, $message='')
    {
        header('content-type: application/json');
        $obj = array(
            'result' => $result ? 'success' : 'failure',
            'message' => $message,
        );
        Zend_Json::$useBuiltinEncoderDecoder = true;
        echo Zend_Json::encode($obj);
        die();
    }

    public function updateAction()
    {
        $action = $this->getRequest()->getParam('action');
        $id = $this->getRequest()->getParam('id');
        try
        {
            $result = true;
            $message = '';
            switch($action)
            {
                case 'cancel':
                    $this->cancelEmail($id);
                    break;
                case 'delete':
                    $this->deleteEmail($id);
                    break;
                case 'send':
                    $this->sendEmail($id);
                    break;
                default:
                    $result = false;
                    $message = 'No action specified';
            }
            $this->sendResponse($result, $message);
        }
        catch (Exception $e)
        {
            Mage::logException($e);
            $this->sendResponse(false, $this->__('Error: %s', $e->getMessage()));
            return;
        }
    }

    public function previewAction()
    {
        if($id = $this->getRequest()->getParam('id'))
        {
            $email = Mage::getModel('followupemail/queue')->load($id);
            if(!$email->getId())
            {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Email does not longer exist'));
                $this->_redirect('*/*/');
                return;
            }
            $rule = Mage::getModel('followupemail/rule')->load($email->getRuleId());

            $from = Mage::getResourceModel('followupemail/queue')->getFromFields($email->getRuleId());
            if(!isset($from['email_send_to_customer'])) $from['email_send_to_customer'] = true;

            if(!$from['email_send_to_customer']
                && AWW_Followupemail_Helper_Data::getCustomSMTPSettings() === FALSE) {
                $_copyTo = Mage::helper('followupemail')->explodeEmailList($from['email_copy_to']);
                $_emailCopyTo = implode(', ', $_copyTo);
            }

            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('core/template')
                    ->setId($id)
                    ->setTemplate('followupemail/preview.phtml')
                    ->setSenderName($email->getSenderName())
                    ->setSenderEmail($email->getSenderEmail())
                    ->setRecipientName($email->getRecipientName())
                    ->setRecipientEmail($email->getRecipientEmail())
                    ->setSubject($email->getSubject())
                    ->setEmailCopyTo(isset($_emailCopyTo) ? $_emailCopyTo : $rule->getEmailCopyTo())
                    ->setContent($email->getContent())
                    ->setStatus($email->getStatus())
                    ->toHtml());
        }
    }

    public function deleteAction()
    {
        if($id = $this->getRequest()->getParam('id'))
        {
            try
            {
                $this->deleteEmail($id);
            }
            catch (Exception $e) 
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('followupemail/log')->logError("email id=$id could not be deleted by Administrator", $this);
            }
        }
        $this->_redirect('*/*/');
    }

    public function cancelAction()
    {
        if($id = $this->getRequest()->getParam('id'))
            try
            {
                $this->cancelEmail($id);
            }
            catch (Exception $e)
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('followupemail/log')->logError("email id=$id could not be cancelled by Administrator", $this);
            }
        $this->_redirect('*/*/');
    }

    public function sendAction() {
        if($id = $this->getRequest()->getParam('id')) {
            try {
                $this->sendEmail($id);
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('followupemail/entries');
    }

    protected function massactionsendAction() {
        $emailIds = $this->getRequest()->getParam('emails');
        $cnt = 0;
        if(is_array($emailIds)) {
            foreach($emailIds as $emailId) {
                $this->sendEmail($emailId, false);
                $cnt++;
            }
            if($cnt) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('followupemail')->__('Total of %d record(s) were successfully sent', $cnt)
                );
            }
        }
        return $this->_redirect('*/*/index');
    }

    protected function massactioncancelAction() {
        $emailIds = $this->getRequest()->getParam('emails');
        $cnt = 0;
        if(is_array($emailIds)) {
            foreach($emailIds as $emailId) {
                $this->cancelEmail($emailId, false);
                $cnt++;
            }
            if($cnt) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('followupemail')->__('Total of %d record(s) were successfully cancelled', $cnt)
                );
            }
        }
        return $this->_redirect('*/*/index');
    }

    protected function massactiondeleteAction() {
        $emailIds = $this->getRequest()->getParam('emails');
        $cnt = 0;
        if(is_array($emailIds)) {
            foreach($emailIds as $emailId) {
                $this->deleteEmail($emailId, false);
                $cnt++;
            }
            if($cnt) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('followupemail')->__('Total of %d record(s) were successfully deleted', $cnt)
                );
            }
        }
        return $this->_redirect('*/*/index');
    }
}
