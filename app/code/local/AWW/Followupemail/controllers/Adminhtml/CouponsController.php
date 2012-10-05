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


class AWW_Followupemail_Adminhtml_CouponsController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        if (Mage::helper('followupemail')->checkVersion('1.4'))
            $this->_title("Manage Coupons");
        return $this->loadLayout()->_setActiveMenu('followupemail/coupons');
    }

    protected function indexAction() {
        $this->_initAction()->renderLayout();
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('followupemail/coupons');
    }

    protected function deleteAction() {
        if($this->getRequest()->getParam('id')) {
            $coupon = Mage::getModel('salesrule/coupon')->load($this->getRequest()->getParam('id'));
            if($coupon->getData()) {
                if(!$coupon->getIsPrimary()) {
                    $this->_getSession()->addSuccess(Mage::helper('followupemail')->__('Coupon "%s" successfully removed', $coupon->getCode()));
                    $coupon->delete();
                } else {
                    $this->_getSession()->addError(Mage::helper('followupemail')->__('Can\'t remove primary coupon'));
                }
            } else {
                $this->_getSession()->addError(Mage::helper('followupemail')->__('Can\'t load coupon by given ID'));
            }
        } else {
            $this->_getSession()->addError(Mage::helper('followupemail')->__('ID isn\'t specified'));
        }

        $this->_redirect('*/*/index');
    }
}
