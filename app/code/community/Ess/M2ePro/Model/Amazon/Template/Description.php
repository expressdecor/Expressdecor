<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_Description extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const TITLE_MODE_PRODUCT = 0;
    const TITLE_MODE_CUSTOM  = 1;

    const BRAND_MODE_NONE   = 0;
    const BRAND_MODE_CUSTOM = 1;

    const MANUFACTURER_MODE_NONE   = 0;
    const MANUFACTURER_MODE_CUSTOM = 1;

    const BULLET_POINTS_MODE_NONE   = 0;
    const BULLET_POINTS_MODE_CUSTOM = 1;

    const DESCRIPTION_MODE_NONE     = 0;
    const DESCRIPTION_MODE_PRODUCT  = 1;
    const DESCRIPTION_MODE_SHORT    = 2;
    const DESCRIPTION_MODE_CUSTOM   = 3;

    const EDITOR_TYPE_SIMPLE  = 0;
    const EDITOR_TYPE_TINYMCE = 1;

    const IMAGE_MAIN_MODE_NONE       = 0;
    const IMAGE_MAIN_MODE_PRODUCT    = 1;
    const IMAGE_MAIN_MODE_ATTRIBUTE  = 2;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_Description');
    }

    // ########################################

    public function getListings($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getListings($asObjects,$filters);
    }

    // ########################################

    public function getTitleMode()
    {
        return (int)$this->getData('title_mode');
    }

    public function isTitleModeProduct()
    {
        return $this->getTitleMode() == self::TITLE_MODE_PRODUCT;
    }

    public function isTitleModeCustom()
    {
        return $this->getTitleMode() == self::TITLE_MODE_CUSTOM;
    }

    public function getTitleSource()
    {
        return array(
            'mode'     => $this->getTitleMode(),
            'template' => $this->getData('title_template')
        );
    }

    public function getTitleAttributes()
    {
        $attributes = array();
        $src = $this->getTitleSource();

        if ($src['mode'] == self::TITLE_MODE_PRODUCT) {
            $attributes[] = 'name';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getBrandMode()
    {
        return (int)$this->getData('brand_mode');
    }

    public function isBrandModeNone()
    {
        return $this->getBrandMode() == self::BRAND_MODE_NONE;
    }

    public function isBrandModeCustom()
    {
        return $this->getBrandMode() == self::BRAND_MODE_CUSTOM;
    }

    public function getBrandSource()
    {
        return array(
            'mode'     => $this->getBrandMode(),
            'template' => $this->getData('brand_template')
        );
    }

    public function getBrandAttributes()
    {
        $attributes = array();
        $src = $this->getBrandSource();

        if ($src['mode'] == self::BRAND_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getManufacturerMode()
    {
        return (int)$this->getData('manufacturer_mode');
    }

    public function isManufacturerModeNone()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_NONE;
    }

    public function isManufacturerModeCustom()
    {
        return $this->getManufacturerMode() == self::MANUFACTURER_MODE_CUSTOM;
    }

    public function getManufacturerSource()
    {
        return array(
            'mode'     => $this->getManufacturerMode(),
            'template' => $this->getData('manufacturer_template')
        );
    }

    public function getManufacturerAttributes()
    {
        $attributes = array();
        $src = $this->getBrandSource();

        if ($src['mode'] == self::MANUFACTURER_MODE_CUSTOM) {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getBulletPointsMode()
    {
        return (int)$this->getData('bullet_points_mode');
    }

    public function isBulletPointsModeNone()
    {
        return $this->getBulletPointsMode() == self::BRAND_MODE_NONE;
    }

    public function isBulletPointsModeCustom()
    {
        return $this->getBulletPointsMode() == self::BRAND_MODE_CUSTOM;
    }

    public function getBulletPointsSource()
    {
        return array(
            'mode'     => $this->getBulletPointsMode(),
            'template' => '' // TODO next release
        );
    }

    public function getBulletPointsAttributes()
    {
        return array(); // TODO next release
    }

    //-------------------------

    public function getDescriptionMode()
    {
        return (int)$this->getData('description_mode');
    }

    public function isDescriptionModeNone()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_NONE;
    }

    public function isDescriptionModeProduct()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_PRODUCT;
    }

    public function isDescriptionModeShort()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_SHORT;
    }

    public function isDescriptionModeCustom()
    {
        return $this->getDescriptionMode() == self::DESCRIPTION_MODE_CUSTOM;
    }

    public function getDescriptionSource()
    {
        return array(
            'mode'     => $this->getDescriptionMode(),
            'template' => $this->getData('description_template')
        );
    }

    public function getDescriptionAttributes()
    {
        $attributes = array();
        $src = $this->getDescriptionSource();

        if ($src['mode'] == self::DESCRIPTION_MODE_PRODUCT) {
            $attributes[] = 'description';
        } elseif ($src['mode'] == self::DESCRIPTION_MODE_SHORT) {
            $attributes[] = 'short_description';
        } else {
            $match = array();
            preg_match_all('/#([a-zA-Z_]+?)#/', $src['template'], $match);
            $match && $attributes = $match[1];
        }

        return $attributes;
    }

    //-------------------------

    public function getEditorType()
    {
        return (int)$this->getData('editor_type');
    }

    public function isEditorTypeSimple()
    {
        return $this->getEditorType() == self::EDITOR_TYPE_SIMPLE;
    }

    public function isEditorTypeTinyMce()
    {
        return $this->getEditorType() == self::EDITOR_TYPE_TINYMCE;
    }

    //-------------------------

    public function getImageMainMode()
    {
        return (int)$this->getData('image_main_mode');
    }

    public function isImageMainModeNone()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_NONE;
    }

    public function isImageMainModeProduct()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_PRODUCT;
    }

    public function isImageMainModeAttribute()
    {
        return $this->getImageMainMode() == self::IMAGE_MAIN_MODE_ATTRIBUTE;
    }

    public function getImageMainSource()
    {
        return array(
            'mode'     => $this->getImageMainMode(),
            'attribute' => $this->getData('image_main_attribute')
        );
    }

    public function getImageMainAttributes()
    {
        $attributes = array();
        $src = $this->getImageMainSource();

        if ($src['mode'] == self::IMAGE_MAIN_MODE_PRODUCT) {
            $attributes[] = 'image';
        } else if ($src['mode'] == self::IMAGE_MAIN_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ########################################

    public function getUsedAttributes()
    {
        return array();
    }

    // ########################################
}