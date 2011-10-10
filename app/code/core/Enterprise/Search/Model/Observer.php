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
 * @package     Enterprise_Search
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

 /**
 * Enterprise search model observer
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Search_Model_Observer
{
    /**
     * Add search weight field to attribute edit form (only for quick search)
     * @see Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main
     *
     * @param Varien_Event_Observer $observer
     */
    public function eavAttributeEditFormInit(Varien_Event_Observer $observer)
    {
        if (Mage::helper('enterprise_search')->isThirdPartSearchEngine()) {
            $form      = $observer->getEvent()->getForm();
            $attribute = $observer->getEvent()->getAttribute();
            $fieldset  = $form->getElement('front_fieldset');

            $fieldset->addField('search_weight', 'select', array(
                'name'        => 'search_weight',
                'label'       => Mage::helper('catalog')->__('Search Weight'),
                'values'      => Mage::getModel('enterprise_search/source_weight')->getOptions(),
            ), 'is_searchable');
            /**
             * Disable default search fields
             */
            $attributeCode = $attribute->getAttributeCode();

            if ($attributeCode == 'name') {
                $form->getElement('is_searchable')->setDisabled(1);
            }
        }
    }

    /**
     * Save search query relations after save search query
     *
     * @param Varien_Event_Observer $observer
     */
    public function searchQueryEditFormAfterSave(Varien_Event_Observer $observer)
    {
        $searchQuryModel = $observer->getEvent()->getDataObject();
        $queryId         = $searchQuryModel->getId();
        $relatedQueries  = $searchQuryModel->getSelectedQueriesGrid();

        if (strlen($relatedQueries) == 0) {
            $relatedQueries = array();
        } else {
            $relatedQueries = explode('&', $relatedQueries);
        }

        Mage::getResourceModel('enterprise_search/recommendations')
            ->saveRelatedQueries($queryId, $relatedQueries);
    }
}
