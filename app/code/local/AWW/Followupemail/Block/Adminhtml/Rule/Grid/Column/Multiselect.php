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


/** Multiselect grid column renderer */

class AWW_Followupemail_Block_Adminhtml_Rule_Grid_Column_Multiselect extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '';
        $value = $row->getData($this->getColumn()->getIndex());
        $valueSeparator = $this->getColumn()->getValueSeparator();
        if(!$valueSeparator) $valueSeparator = ',';
        $lineSeparator = $this->getColumn()->getLineSeparator();
        $options = $this->getColumn()->getOptions();

        $values = explode($valueSeparator, $value);

        foreach($values as $v)
        {
            $html .= $lineSeparator;
            if(array_key_exists($v, $options)) $html .= $options[$v];
            else $html .= $value;
        }
        // removing first $lineSeparator
        if($html && $lineSeparator) $html = substr($html, strlen($lineSeparator));

        return $html;
    }

}
