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


class AWW_Followupemail_Model_Source_Customer_Group
{
    const CUSTOMER_GROUP_ALL = 'ALL';
    const CUSTOMER_GROUP_NOT_REGISTERED = 'NOT_REGISTERED';

    public static function toOptionArray()
    {
        // $res = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
        $res = Mage::helper('customer')->getGroups()->toOptionArray();

        $found = false;
        foreach ($res as $group)
            if ($group['value']==0)
            {
                $found = true;
                break;
            }
        if (!$found) array_unshift($res, array('value' => self::CUSTOMER_GROUP_NOT_REGISTERED, 'label'=>Mage::helper('followupemail')->__('Not registered')));

        array_unshift($res, array('value' => self::CUSTOMER_GROUP_ALL, 'label' => Mage::helper('followupemail')->__('All groups')));

        return $res;
    }
}