<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Customer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Customer Attribute Form Renderer Block for Date
 *
 * @category    Enterprise
 * @package     Enterprise_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Customer_Block_Form_Renderer_Date extends Enterprise_Customer_Block_Form_Renderer_Abstract
{
    /**
     * Array of date parts html fragments keyed by date part code
     *
     * @var array
     */
    protected $_dateInputs  = array();

    /**
     * Returns format which will be applied for date field in javascript
     *
     * @return string
     */
    public function getDateFormat()
    {
        return Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    /**
     * Add date input html
     *
     * @param string $code
     * @param string $html
     */
    public function setDateInput($code, $html)
    {
        $this->_dateInputs[$code] = $html;
    }

    /**
     * Sort date inputs by dateformat order of current locale
     *
     * @return string
     */
    public function getSortedDateInputs()
    {
        $strtr = array(
            '%b' => '%1$s',
            '%B' => '%1$s',
            '%m' => '%1$s',
            '%d' => '%2$s',
            '%e' => '%2$s',
            '%Y' => '%3$s',
            '%y' => '%3$s'
        );

        $dateFormat = preg_replace('/[^\%\w]/', '\\1', $this->getDateFormat());

        return sprintf(strtr($dateFormat, $strtr),
            $this->_dateInputs['m'], $this->_dateInputs['d'], $this->_dateInputs['y']);
    }

    /**
     * Return value as unix time stamp or false
     *
     * @return int|false
     */
    public function getTimestamp()
    {
        $timestamp         = $this->getData('timestamp');
        $attributeCodeThis = $this->getData('attribute_code');
        $attributeCodeObj  = $this->getAttributeObject()->getAttributeCode();
        if (is_null($timestamp) || $attributeCodeThis != $attributeCodeObj) {
            $value = $this->getValue();
            if ($value) {
                if (is_numeric($value)) {
                    $timestamp = $value;
                } else {
                    $timestamp = strtotime($value);
                }
            } else {
                $timestamp = false;
            }
            $this->setData('timestamp', $timestamp);
            $this->setData('attribute_code', $attributeCodeObj);
        }
        return $timestamp;
    }

    /**
     * Return Date part by index
     *
     * @param string $index allowed index (Y,m,d)
     * @return string
     */
    protected function _getDateValue($index)
    {
        if ($this->getTimestamp()) {
            return date($index, $this->getTimestamp());
        }
        return '';
    }

    /**
     * Return day value from date
     *
     * @return string
     */
    public function getDay()
    {
        return $this->_getDateValue('d');
    }

    /**
     * Return month value from date
     *
     * @return string
     */
    public function getMonth()
    {
        return $this->_getDateValue('m');
    }

    /**
     * Return year value from date
     *
     * @return string
     */
    public function getYear()
    {
        return $this->_getDateValue('Y');
    }
}
