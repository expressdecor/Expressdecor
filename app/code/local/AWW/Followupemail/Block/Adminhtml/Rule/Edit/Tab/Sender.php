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


class AWW_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Sender extends Mage_Adminhtml_Block_Widget_Form 
{
    protected function _prepareForm() 
    {
        $data = Mage::registry('followupemail_data');

        if (is_object($data)) $data = $data->getData();

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('followupemail_sender_details', array('legend' => $this->__('Sender Details')));

        # sender_name field
        $fieldset->addField('sender_name', 'text',
            array(
                'label' => $this->__('Sender name'),
                'name'  => 'sender_name',
            ));

        # sender_email field
        $fieldset->addField('sender_email', 'text', array(
                'label' => $this->__('Sender email'),
                'name'  => 'sender_email',
                'after_element_html' => 
                    '<span class="note"><small>'
                        .$this->__('Redefines sender for this rule. Sender from the general settings is used by default')
                    .'</small></span>',
            ));

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}