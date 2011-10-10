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
 * @package     Enterprise_Banner
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Banner Widget Block
 *
 * @category   Enterprise
 * @package    Enterprise_Banner
 */
class Enterprise_Banner_Block_Widget_Banner
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    /**
     * Display mode "fixed" flag
     *
     */
    const BANNER_WIDGET_DISPLAY_FIXED = 'fixed';

    /**
     * Display mode "salesrule" flag
     *
     */
    const BANNER_WIDGET_DISPLAY_SALESRULE = 'salesrule';

    /**
     * Display mode "catalogrule" flag
     *
     */
    const BANNER_WIDGET_DISPLAY_CATALOGRULE = 'catalogrule';

    /**
     * Rotation mode "series" flag: output one of banners sequentially per visitor session
     *
     */
    const BANNER_WIDGET_RORATE_SERIES = 'series';

    /**
     * Rotation mode "random" flag: output one of banners randomly
     *
     */
    const BANNER_WIDGET_RORATE_RANDOM = 'random';

    /**
     * Rotation mode "shuffle" flag: same as "series" but firstly randomize banenrs scope
     *
     */
    const BANNER_WIDGET_RORATE_SHUFFLE = 'shuffle';

    /**
     * Store Banner resource instance
     *
     * @var Enterprise_Banner_Model_Mysql4_Banner
     */
    protected $_bannerResource = null;

    /**
     * Store visitor session instance
     *
     * @var Mage_Core_Model_Session
     */
    protected $_sessionInstance = null;

    /**
     * Store current store ID
     *
     * @var int
     */
    protected $_currentStoreId = null;

    /**
     * Define default template, load Banner resource, get session instance and set current store ID
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_bannerResource  = Mage::getResourceSingleton('enterprise_banner/banner');
        $this->_currentStoreId  = Mage::app()->getStore()->getId();
        $this->_sessionInstance = Mage::getSingleton('core/session');
    }

    /**
     * Set default display mode if its not set
     *
     * @return string
     */
    public function getDisplayMode()
    {
        if (!$this->_getData('display_mode')) {
            $this->setData('display_mode', self::BANNER_WIDGET_DISPLAY_FIXED);
        }
        return $this->_getData('display_mode');
    }

    /**
     * Retrive converted to an array and filtered parameter "banner_ids"
     *
     * @return array
     */
    public function getBannerIds()
    {
        if (!$this->_getData('banner_ids')) {
            $this->setData('banner_ids', array(0));
        }
        elseif (is_string($this->_getData('banner_ids'))) {
            $bannerIds = explode(',', $this->_getData('banner_ids'));
            foreach ($bannerIds as $_key => $_id) {
                $bannerIds[$_key] = (int)trim($_id);
            }
            $bannerIds = $this->_bannerResource->getExistingBannerIdsBySpecifiedIds($bannerIds);
            $this->setData('banner_ids', $bannerIds);
        }

        return $this->_getData('banner_ids');
    }

    /**
     * Retrieve right rotation mode or return null
     *
     * @return string|null
     */
    public function getRotate()
    {
        if (!$this->_getData('rotate') || ($this->_getData('rotate') != self::BANNER_WIDGET_RORATE_RANDOM &&
                                           $this->_getData('rotate') != self::BANNER_WIDGET_RORATE_SERIES &&
                                           $this->_getData('rotate') != self::BANNER_WIDGET_RORATE_SHUFFLE
                                           )) {
            $this->setData('rotate', null);
        }
        return $this->_getData('rotate');
    }

    /**
     * Set unique id of widget instance if its not set
     *
     * @return string
     */
    public function getUniqueId()
    {
        if (!$this->_getData('unique_id')){
            $this->setData('unique_id', md5(implode('-', $this->getBannerIds())));
        }
        return $this->_getData('unique_id');
    }

    /**
     * Get banner(s) content to display
     *
     * @return array
     */
    public function getBannersContent()
    {
        $bannersContent = array();
        $aplliedRules = null;
        $segmentIds = array();
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $segmentIds = Mage::getSingleton('enterprise_customersegment/customer')->getCustomerSegmentIds(
                Mage::getSingleton('customer/session')->getCustomer()
            );
        }

        $this->_bannerResource->filterByTypes($this->getTypes());

        // choose display mode
        switch ($this->getDisplayMode()) {

            case self::BANNER_WIDGET_DISPLAY_SALESRULE:
                if (Mage::getSingleton('checkout/session')->getQuoteId()) {
                    $aplliedRules = explode(',', Mage::getSingleton('checkout/session')->getQuote()->getAppliedRuleIds());
                }
                $bannerIds = $this->_bannerResource->getSalesRuleRelatedBannerIds($segmentIds, $aplliedRules);
                $bannersContent = $this->_getBannersContent($bannerIds);
                break;

            case self::BANNER_WIDGET_DISPLAY_CATALOGRULE:
                $bannerIds = $this->_bannerResource->getCatalogRuleRelatedBannerIds(
                    Mage::app()->getWebsite()->getId(),
                    Mage::getSingleton('customer/session')->getCustomerGroupId(),
                    $segmentIds
                );
                $bannersContent = $this->_getBannersContent($bannerIds);
                break;

            case self::BANNER_WIDGET_DISPLAY_FIXED:
            default:
                $bannersContent = $this->_getBannersContent($this->getBannerIds(), $segmentIds);
                break;
        }

        $this->_bannerResource->filterByTypes(); // unset types filter from resource


        // filtering directives
        /* @var $helper Mage_Cms_Helper_Data */
        $helper = Mage::helper('cms');
        $processor = $helper->getPageTemplateProcessor();
        foreach ($bannersContent as $bannerId => $content) {
            $bannersContent[$bannerId] = $processor->filter($content);
        }
        return $bannersContent;
    }

    /**
     * Get banners content by specified banners IDs depend on Rotation mode
     *
     * @param array $bannerIds
     * @param array $segmentIds
     * @param int $storeId
     * @return array
     */
    protected function _getBannersContent($bannerIds, $segmentIds = array())
    {
        $bannersSequence = $content = array();
        if (!empty($bannerIds)) {

            //Choose rotation mode
            switch ($this->getRotate()) {

                case self::BANNER_WIDGET_RORATE_RANDOM :
                    $bannerId = $bannerIds[array_rand($bannerIds, 1)];
                    $_content = $this->_bannerResource->getStoreContent($bannerId, $this->_currentStoreId, $segmentIds);
                    if (!empty($_content)) {
                        $content[$bannerId] = $_content;
                    }
                    break;
                case self::BANNER_WIDGET_RORATE_SHUFFLE :
                case self::BANNER_WIDGET_RORATE_SERIES :
                    $bannerId = $bannerIds[0];
                    if (!$this->_sessionInstance->_getData($this->getUniqueId())) {
                        $this->_sessionInstance->setData($this->getUniqueId(), array($bannerIds[0]));
                    }
                    else {
                        $bannersSequence = $this->_sessionInstance->_getData($this->getUniqueId());
                        $canShowIds = array_merge(array_diff($bannerIds, $bannersSequence), array());
                        if (!empty($canShowIds)) {
                            $showId = 0;
                            if ($this->getRotate() == self::BANNER_WIDGET_RORATE_SHUFFLE) {
                                $showId = array_rand($canShowIds, 1);
                            }
                            $bannersSequence[] = $canShowIds[$showId];
                            $bannerId = $canShowIds[$showId];
                        }
                        else {
                            $bannersSequence = array($bannerIds[0]);
                        }
                        $this->_sessionInstance->setData($this->getUniqueId(), $bannersSequence);
                    }
                    $_content = $this->_bannerResource->getStoreContent($bannerId, $this->_currentStoreId, $segmentIds);
                    if (!empty($_content)) {
                        $content[$bannerId] = $_content;
                    }
                    break;

                default:
                    $content = $this->_bannerResource->getBannersContent($bannerIds, $this->_currentStoreId, $segmentIds);
                    break;
            }
        }
        return $content;
    }
}
