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


class AWW_Followupemail_IndexController extends Mage_Core_Controller_Front_Action
{
    /*
     * Unsubscribes customer
     */
    public function unsubscribeAction()
    {
        if ($code = $this->getRequest()->getParam('code')) {
            if (!$queue = Mage::getModel('followupemail/queue')->loadByCode($code)) {
                Mage::getSingleton('core/session')->addError($this->__('Wrong unsubscription code specified'));
                $this->_redirect('/');
                return;
            }
            if ($queue->getParam('customer_id') && $queue->getRuleId()) {
                $rule = Mage::getModel('followupemail/rule')->load($queue->getRuleId());
                $rule->unsubscribeCustomer($queue->getParam('customer_id'))->save();
                $customerEmail = $queue->getData('recipient_email');
                if($customerEmail) {
                    // Cancel all scheduled and 'Ready to go' messages to this email
                    $queuedEmails = Mage::getModel('followupemail/queue')->getCollection();
                    $queuedEmails->addFieldToFilter('status', AWW_Followupemail_Model_Source_Queue_Status::QUEUE_STATUS_READY);
                    $queuedEmails->addFieldToFilter('rule_id', $rule->getId());
                    $queuedEmails->addFieldToFilter('recipient_email', $customerEmail);
                    foreach($queuedEmails as $email) {
                        $email->cancel();
                    }
                }
                Mage::getSingleton('core/session')->addSuccess($this->__('You has been successfully unsubscribed from receiving the same messages'));
                Mage::getSingleton('followupemail/log')->logWarning('unsubscribe rule action, customer ' . $queue->getParam('customer_id') . ' rule ' . $queue->getRuleId(), $this);
            }
        }
        if ($goto = urldecode($this->getRequest()->getParam('goto'))) {
            $this->_redirect($goto);
        } else {
            $this->_redirect('/');
        }
    }

    /*
     * Restores customer session and (if rule type was 'Cart has been abandened') customer's abandoned cart
     */
    public function resumeAction()
    {
        if ($code = $this->getRequest()->getParam('code')) {
            if (!$queue = Mage::getModel('followupemail/queue')->loadByCode($code)) {
                Mage::getSingleton('core/session')->addError($this->__('Wrong resume code specified'));
                $this->_redirect('/');
                return;
            }

            Mage::getModel('followupemail/linktracking')
                ->setId(null)
                ->setQueueId($queue->getId())
                ->setVisitedAt(date(AWW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, time()))
                ->setVisitedFrom(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')
                ->save();

            $rule = Mage::getModel('followupemail/rule')->load($queue->getRuleId());

            if (AWW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW == $rule->getEventType()) {
                if ($quoteId = $queue->getObjectId()) {
                    $quote = Mage::getModel('sales/quote')->load($quoteId);
                    Mage::getSingleton('checkout/session')->replaceQuote($quote);

                    Mage::getSingleton('followupemail/log')->logSuccess('abandoned cart restored, cart_id=' . $quoteId . ', queue_id=' . $queue->getId(), $this);
                }
            }

            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($queue->getRecipientEmail());

            if ($customerId = $customer->getId()) {
                $session = Mage::getSingleton('customer/session');
                if ($session->isLoggedIn() && $customerId != $session->getCustomerId())
                    $session->logout();

                try {
                    $session->setCustomerAsLoggedIn($customer);
                } catch (Exception $ex) {
                    Mage::getSingleton('core/session')->addError($this->__('Your account isn\'t confirmed'));
                    $this->_redirect('/');
                }
            }
            Mage::getModel('followupemail/events')->customerCameBack($queue);
            $tracking = Mage::helper('followupemail')->getGaConfig($rule);
            if ($goto = urldecode($this->getRequest()->getParam('goto')))
                $this->getResponse()->setRedirect(Mage::getUrl($goto) . $tracking);
            else $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart') . $tracking);
        }
        else
        {
            Mage::getSingleton('core/session')->addError($this->__('No resume code cpecified'));
            $this->_redirect('/');
        }
    }
}
