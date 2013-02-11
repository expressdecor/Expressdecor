<?php
/**
 * Magento Order Editor Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the License Version.
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 *
 * @category   Order Editor
 * @package    Oeditor_Ordereditor
 * @copyright  Copyright (c) 2010 
 * @version    0.4.1
*/


class Oeditor_Ordereditor_Block_Adminhtml_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info {
	
	public function __construct() {
		parent::__construct();
		$this->setTemplate('ordereditor/ordereditor.phtml');
	}

	protected function _beforeToHtml() {
		parent::_beforeToHtml();
		$this->setTemplate('ordereditor/ordereditor.phtml');
	}
}