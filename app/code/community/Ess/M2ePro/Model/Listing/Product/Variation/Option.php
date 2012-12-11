<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Product_Variation_Option extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product_Variation
     */
    private $listingProductVariationModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    protected $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product_Variation_Option');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->listingProductVariationModel = NULL;
        $temp && $this->magentoProductModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product_Variation
     */
    public function getListingProductVariation()
    {
        if (is_null($this->listingProductVariationModel)) {
            $this->listingProductVariationModel = Mage::helper('M2ePro/Component')->getComponentObject(
                $this->getComponentMode(),'Listing_Product_Variation',$this->getData('listing_product_variation_id')
            );
        }

        return $this->listingProductVariationModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Variation $instance
     */
    public function setListingProductVariation(Ess_M2ePro_Model_Listing_Product_Variation $instance)
    {
         $this->listingProductVariationModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        if ($this->magentoProductModel) {
            return $this->magentoProductModel;
        }

        return $this->magentoProductModel = Mage::getModel('M2ePro/Magento_Product')
                ->setStoreId($this->getListing()->getStoreId())
                ->setProductId($this->getData('product_id'));
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $instance)
    {
        $this->magentoProductModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getListingProductVariation()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->getListingProductVariation()->getListingProduct();
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_General
     */
    public function getGeneralTemplate()
    {
        return $this->getListingProductVariation()->getGeneralTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getListingProductVariation()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getListingProductVariation()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getListingProductVariation()->getSynchronizationTemplate();
    }

    // ########################################

    public function getListingProductVariationId()
    {
        return (int)$this->getData('listing_product_variation_id');
    }

    public function getProductId()
    {
        return (int)$this->getData('product_id');
    }

    public function getProductType()
    {
        return $this->getData('product_type');
    }

    //----------------------------------------

    public function getAttribute()
    {
         return $this->getData('attribute');
    }

    public function getOption()
    {
        return $this->getData('option');
    }

    // ########################################

    public function getChangedItemsByAttributes(array $attributes, $componentMode = NULL)
    {
        if (count($attributes) <= 0) {
            return array();
        }

        $productsChangesTable = Mage::getResourceModel('M2ePro/ProductChange')->getMainTable();
        $optionsTable = Mage::getResourceModel('M2ePro/Listing_Product_Variation_Option')->getMainTable();

        $fields = array(
            'pc_id'=>'id',
            'pc_attribute'=>'attribute',
            'pc_value_old'=>'value_old',
            'pc_value_new'=>'value_new',
            'pc_count_changes'=>'count_changes'
        );
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->distinct()
                             ->from(array('pc' => $productsChangesTable),$fields)
                             ->join(array('lpvo' => $optionsTable),'`pc`.`product_id` = `lpvo`.`product_id`','*')
                             ->where('`pc`.`action` = ?',(string)Ess_M2ePro_Model_ProductChange::ACTION_UPDATE)
                             ->where("`pc`.`attribute` IN ('".implode("','",$attributes)."')");

        !is_null($componentMode) && $dbSelect->where("`lpvo`.`component_mode` = ?",(string)$componentMode);

        return Mage::getResourceModel('core/config')
                            ->getReadConnection()
                            ->fetchAll($dbSelect);
    }

    // ########################################
}