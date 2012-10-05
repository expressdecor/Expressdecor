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


class AWW_Followupemail_Model_Source_Rule_Saleamount
{
    const CONDITION_SEPARATOR = ' ';

    // const CONDITION_EQ  = '=';
    // const CONDITION_GT  = '>';
    // const CONDITION_EGT = '>=';
    // const CONDITION_LT  = '<';
    // const CONDITION_ELT = '<=';
    // const CONDITION_NE  = '<>';

    const CONDITION_EQ  = 1;
    const CONDITION_GT  = 2;
    const CONDITION_EGT = 3;
    const CONDITION_LT  = 4;
    const CONDITION_ELT = 5;
    const CONDITION_NE  = 6;


    public function toOptionArray($addSelectOption = false)
    {
        $helper = Mage::helper('followupemail');

        $res = array();

        if($addSelectOption) $res[0] = $helper->__('Doesn\'t matter');

        $res = array_merge($res, array(
            self::CONDITION_EQ  => $helper->__('Equals to'),
            self::CONDITION_GT  => $helper->__('Greater than'),
            self::CONDITION_EGT => $helper->__('Equals or greater than'),
            self::CONDITION_LT  => $helper->__('Less than'),
            self::CONDITION_ELT => $helper->__('Equals or less than'),
            self::CONDITION_NE  => $helper->__('Not equals to'),
        ));

        return $res;
    }

    public static function getCondition($id = null)
    {
        $conditions = self::getConditions();
        if(is_null($id) || !array_key_exists($id, $conditions)) return 0;
        else return $conditions[$id];
    }

    public static function getConditions()
    {
        return array(
            self::CONDITION_EQ  => '=',
            self::CONDITION_GT  => '>',
            self::CONDITION_EGT => '>=',
            self::CONDITION_LT  => '<',
            self::CONDITION_ELT => '<=',
            self::CONDITION_NE  => '<>',
        );
    }
}