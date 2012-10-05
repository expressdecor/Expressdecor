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

class AW_Core_ViewlogController extends Mage_Adminhtml_Controller_action {
    public function indexAction() {
	$this
		->loadLayout()
		->_addContent($this->getLayout()->createBlock('awcore/adminhtml_log'))
		->renderLayout();
    }
    /**
     * Clears all records in log table
     * @return AW_Core_ViewlogController
     */
    public function clearAction() {
	try {
	    Mage::getResourceSingleton('awcore/logger')->truncateAll();
	    Mage::getSingleton('adminhtml/session')->addSuccess("Log successfully cleared");
	    $this->_redirect('*/*');

	}catch(Mage_Core_Exception $E) {
	    Mage::getSingleton('adminhtml/session')->addError($E->getMessage());
	    $this->_redirectReferer();
	}
	return $this;
    }

}