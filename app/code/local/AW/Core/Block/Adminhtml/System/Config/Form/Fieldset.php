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

class AW_Core_Block_Adminhtml_System_Config_Form_Fieldset extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    /**
     * Render fieldset html
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
	$html = $this->_getHeaderHtml($element);

	foreach ($element->getElements() as $field) {
	    $html.= $field->toHtml();
	}
	$html .= "<tr>
			<td class=\"label\"></td>
			<td class=\"value\">
			<button class=\"scalable\" onclick=\"window.location='".Mage::getSingleton('adminhtml/url')->getUrl('awcore_admin/viewlog/index')."'\" type=\"button\">
				<span>View log</span>
			</button
			</td>
		 </tr>
		 ";
	$html .= $this->_getFooterHtml($element);

	return $html;
    }
}