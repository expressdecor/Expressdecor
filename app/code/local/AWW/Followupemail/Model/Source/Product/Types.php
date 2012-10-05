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


class AWW_Followupemail_Model_Source_Product_Types
{
    const PRODUCT_TYPE_ALL = 'all';

    public function toShortOptionArray()
    {
        $productTypesArray = Mage::getConfig()->getNode('global/catalog/product/type')->asArray();
        $productTypes = array();

        $productTypes[self::PRODUCT_TYPE_ALL] = Mage::helper('followupemail')->__('All types');

        foreach($productTypesArray as $typeCode => $typeArray)
            $productTypes[$typeCode] = trim(substr($typeArray['label'], 0, (false !== $p = strpos($typeArray['label'], ' Product')) ? $p : 999));

        return $productTypes;
    }

    public function toOptionArray()
    {
        foreach($this->toShortOptionArray() as $k => $v)
            $productTypes[] = array(
                'value' => $k,
                'label' => $v
            );

        return $productTypes;
    }
}