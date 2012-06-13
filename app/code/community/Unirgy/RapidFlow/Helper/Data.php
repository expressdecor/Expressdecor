<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_RapidFlow_Helper_Data extends Mage_Core_Helper_Data
{
    public function run($profileId, $stopIfRunning=true, array $updateData=array())
    {
        $profile = Mage::getModel('urapidflow/profile');

        if (is_numeric($profileId)) {
            $profile->load($profileId);
        } else {
            $profile->load($profileId, 'title');
        }

        if (!$profile->getId()) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Invalid Profile ID'));
        }

        $profile = $profile->factory();

        if ($stopIfRunning) {
            try { $profile->stop(); } catch (Exception $e) { };
        }
        
        if (!empty($updateData)) {
            foreach ($updateData as $k=>$v) {
                if (is_array($v)) {
                    $profile->setData($k, array_merge_recursive($profile->getData($k), $v));
                } else {
                    $profile->setData($k, $v);
                }
            }
        }

        $profile->start()->save()->run();

        return $profile;
    }

    public function addAdminhtmlVersion($module='Unirgy_RapidFlow')
    {
        $layout = Mage::app()->getLayout();
        $version = (string)Mage::getConfig()->getNode("modules/{$module}/version");

        $layout->getBlock('before_body_end')->append($layout->createBlock('core/text')->setText('
            <script type="text/javascript">$$(".legality")[0].insert({after:"'.$module.' ver. '.$version.', "});</script>
        '));

        return $this;
    }

    public function formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    public function isModuleActive($code)
    {
        $module = Mage::getConfig()->getNode("modules/$code");
        $model = Mage::getConfig()->getNode("global/models/$code");
        return $module && $module->is('active') || $model;
    }
    public function compareMageVer($ceVer, $eeVer=null, $op='>=')
    {
        return $this->isModuleActive('Enterprise_Enterprise')
            ? version_compare(Mage::getVersion(), !is_null($eeVer) ? $eeVer : $ceVer, $op)
            : version_compare(Mage::getVersion(), $ceVer, $op);
    }

    protected $_hasMageFeature = array();
    public function hasMageFeature($feature)
    {
        if (!isset($this->_hasMageFeature[$feature])) {
            $flag = false;
            switch ($feature) {
           	case 'sales_flat':
                $flag = $this->compareMageVer('1.4.1.0', '1.8.0', '>=');
                break;
            case 'attr.used_in_product_listing':
                $flag = $this->compareMageVer('1.3.0');
                break;

            case 'attr.is_used_for_promo_rules':
                $flag = $this->compareMageVer('1.4.1.0', '1.9.0.0');
                break;

            case 'flat_catalog':
            case 'cpsap.website_id':
                $flag = $this->compareMageVer('1.3.1');
                break;

            case 'product.required_options':
                $flag = $this->compareMageVer('1.3.2');
                break;

            case 'attr.is_used_for_price_rules':
                $flag = $this->compareMageVer('1.4.0');
                break;

            case 'product.category_ids':
                $flag = $this->compareMageVer('1.4', '1.6', '<');
                break;

            case 'indexer_1.4':
            case 'table.product_relation':
            case 'table.eav_attribute_label':
            case 'table.catalog_eav_attribute':
            case 'attr.is_wysiwyg_enabled':
                $flag = $this->compareMageVer('1.4', '1.6');
                break;

            case 'category.include_in_menu':
                $flag = $this->compareMageVer('1.4.1', '1.8');
                break;
            }
            $this->_hasMageFeature[$feature] = $flag;
        }
        return $this->_hasMageFeature[$feature];
    }
}