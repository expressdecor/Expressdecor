<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_AttributeSet extends Ess_M2ePro_Model_Abstract
{
    const OBJECT_TYPE_LISTING                 = 1;
    const OBJECT_TYPE_TEMPLATE_GENERAL        = 2;
    const OBJECT_TYPE_TEMPLATE_SELLING_FORMAT = 3;
    const OBJECT_TYPE_TEMPLATE_DESCRIPTION    = 4;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/AttributeSet');
    }

    // ########################################

    public function getObjectId()
    {
        return (int)$this->getData('object_id');
    }

    public function getObjectType()
    {
        return (int)$this->getData('object_type');
    }

    public function getAttributeSetId()
    {
        return (int)$this->getData('attribute_set_id');
    }

    // ########################################

    public function isListing()
    {
        return $this->getObjectType() == self::OBJECT_TYPE_LISTING;
    }

    public function isGeneralTemplate()
    {
        return $this->getObjectType() == self::OBJECT_TYPE_TEMPLATE_GENERAL;
    }

    public function isSellingFormatTemplate()
    {
        return $this->getObjectType() == self::OBJECT_TYPE_TEMPLATE_SELLING_FORMAT;
    }

    public function isDescriptionTemplate()
    {
        return $this->getObjectType() == self::OBJECT_TYPE_TEMPLATE_DESCRIPTION;
    }

    // ########################################
}