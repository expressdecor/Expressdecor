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


class AWW_Followupemail_Model_Observer
{
    /*
     * Runs after customer checkout, subscribes customer
     */
    public function processSubscription()
    {
        if(!Mage::app()->getRequest()->getPost('newsletter-subscribed')) return;

        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        $customer = $quote->getCustomer();
        $email = $quote->getBillingAddress()->getEmail();

        $subscriber = Mage::getModel('newsletter/subscriber');
        $session = Mage::getSingleton('core/session');

        try
        {
            $subscriber->subscribe($email);
            if($subscriber->getIsStatusChanged())
                $session->addSuccess(Mage::helper('followupemail')->__('You have been subscribed to newsletters'));
        }
        catch (Exception $e) {
            $session->addException($e, Mage::helper('followupemail')->__('There was a problem with the newsletter subscription')
                            .($e instanceof Mage_Core_Exception) ? ': '.$e->getMessage() : ''); }
    }

}