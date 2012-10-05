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


class AWW_Followupemail_Block_Agreements extends Mage_Checkout_Block_Agreements
{
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        $session = Mage::getSingleton('core/session');
        $visitorData = $session->getVisitorData();
        if(isset($visitorData['customer_id']) && ($customerId = $visitorData['customer_id']))
        {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
            $subscribed = $subscriber->isSubscribed();
        }
        else $subscribed = false;

        if(!$subscribed) // if the customer is not subscribed then show him the checkbox
        {
            $this->setIsSubscribed(false);
            $this->setTemplate('followupemail/checkout_newsletter_subscribe.phtml');
            $html .= parent::_toHtml();
        }

        return $html;
    }
}