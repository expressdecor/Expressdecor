<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Component_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // ########################################

    public function getComponentRowUrl($row, $controller, $action, array $params = array())
    {
        $mode = strtolower($row->getData('component_mode'));
        $action = strtolower($action);

        return $this->getUrl("*/adminhtml_{$mode}_{$controller}/$action", $params);
    }

    protected function getFilterOptionsByModel($modelName, $valueField = 'id', $labelField = 'title')
    {
        /** @var $helper Ess_M2ePro_Helper_Component */
        $helper = Mage::helper('M2ePro/Component');
        $collection = Mage::getModel('M2ePro/' . $modelName)->getCollection()->setOrder('title', 'ASC');

        // --------------
        if (count($helper->getActiveComponents()) == 1) {
            if (Mage::helper('M2ePro/Component_Ebay')->isActive()) {
                $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
            }
            if (Mage::helper('M2ePro/Component_Amazon')->isActive()) {
                $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);
            }
        }
        // --------------

        // Prepare options and groups
        // --------------
        $optionGroups = $tempEbayOptions = $tempAmazonOptions = array();

        $options = array();
        foreach ($collection as $item) {
            $options[$item->getData($valueField)] = $item->getData($labelField);

            if (count($helper->getActiveComponents()) > 1) {
                $tempOption = array(
                    'value' => $item->getData($valueField),
                    'label' => $item->getData($labelField)
                );

                $item->isComponentModeEbay()   && $tempEbayOptions[] = $tempOption;
                $item->isComponentModeAmazon() && $tempAmazonOptions[] = $tempOption;
            }
        }

        if (count($helper->getActiveComponents()) > 1) {
            $optionGroups = $this->getComponentFilterGroups($tempEbayOptions, $tempAmazonOptions);
        }
        // --------------

        return array(
            'options'       => $options,
            'option_groups' => $optionGroups
        );
    }

    protected function getComponentFilterGroups(array $ebayOptions = array(), array $amazonOptions = array())
    {
        return array(
            'ebay' => array(
                'label' => Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE),
                'value' => $ebayOptions
            ),
            'amazon' => array(
                'label' => Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE),
                'value' => $amazonOptions
            )
        );
    }

    // ########################################
}