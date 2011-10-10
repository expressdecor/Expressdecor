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
 * @package     Enterprise_GiftCard
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

class Enterprise_GiftCard_Block_Checkout_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
    /**
     * Prepare custom option for display, returns false if there's no value
     *
     * @param string $code
     * @return mixed
     */
    protected function _prepareCustomOption($code)
    {
        if ($option = $this->getItem()->getOptionByCode($code)) {
            if ($value = $option->getValue()) {
                return nl2br($this->htmlEscape($value));
            }
        }
        return false;
    }

    /**
     * Get gift card option list
     *
     * @return array
     */
    protected function _getGiftcardOptions()
    {
        $result = array();
        if ($value = $this->_prepareCustomOption('giftcard_sender_name')) {
            if ($email = $this->_prepareCustomOption('giftcard_sender_email')) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = array(
                'label'=>Mage::helper('enterprise_giftcard')->__('Gift Card Sender'),
                'value'=>$value,
            );
        }
        if ($value = $this->_prepareCustomOption('giftcard_recipient_name')) {
            if ($email = $this->_prepareCustomOption('giftcard_recipient_email')) {
                $value = "{$value} &lt;{$email}&gt;";
            }
            $result[] = array(
                'label'=>Mage::helper('enterprise_giftcard')->__('Gift Card Recipient'),
                'value'=>$value,
            );
        }
        if ($value = $this->_prepareCustomOption('giftcard_message')) {
            $result[] = array(
                'label'=>Mage::helper('enterprise_giftcard')->__('Gift Card Message'),
                'value'=>$value,
            );
        }
        return $result;
    }

    /**
     * Return gift card and custom options array
     *
     * @return array
     */
    public function getOptionList()
    {
        return array_merge($this->_getGiftcardOptions(), parent::getOptionList());
    }
}
