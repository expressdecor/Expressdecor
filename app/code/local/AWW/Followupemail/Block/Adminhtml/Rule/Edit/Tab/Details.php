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


class AWW_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Details extends Mage_Adminhtml_Block_Widget_Form 
{
    protected function _prepareForm() 
    {
        $data = Mage::registry('followupemail_data');

        if (is_object($data)) $data = $data->getData();

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('stores_product_types', array('legend' => $this->__('Stores & Product Types')));

        # store_ids field
        if (!Mage::app()->isSingleStoreMode())
            $fieldset->addField('store_ids', 'multiselect', array(
                'name'      => 'store_ids[]',
                'label'     => $this->__('Store View'),
                'title'     => $this->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        else {
            if(isset($data['store_ids'])) {
                if (is_array($data['store_ids'])) {
                    if (isset($data['store_ids'][0])) $data['store_ids'] = $data['store_ids'][0];
                    else $data['store_ids'] = '';
                }
            }

            $fieldset->addField('store_ids', 'hidden', array(
                'name'      => 'store_ids[]',
                'value'     => Mage::app()->getStore(true)->getId(),
            ));
        }

        # product_type_ids field
        $fieldset->addField('product_type_ids', 'multiselect', array(
                'label'  => $this->__('Product type'),
                'name'   => 'product_type_ids[]',
                'required'  => true,
                'values' => Mage::getModel('followupemail/source_product_types')->toOptionArray(true),
            ));

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}