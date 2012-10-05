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

class AW_Core_Helper_Extension extends Varien_Object {

    /**
     * Detect if extension installed
     * @param string $code
     * @return bool
     */
    public function isExtensionInstalled($code){
        $exts = $this->getInstalledExtensions();
        return (isset($exts[$code]));
    }

    /**
     * Detect if extension is installed and active
     * @param string $code
     * @return bool
     */
    public function isExtensionActive($code){
        if($this->isExtensionInstalled($code)){
            $exts = $this->getInstalledExtensions();
            return (bool)$exts[$code]['active'];
        }
    }

    /**
     * Return all installed extensions
     * This way is based on
     * @return array
     */
    public function getInstalledExtensions(){
        if(!$this->getData('installed_extensions')){
            $exts = array();
            $modules = ((array)Mage::getConfig()->getNode('modules')->children());
            foreach($modules as $k=>$Module){
                $exts[$k] = (array)$Module;
            }
            $this->setData('installed_extensions', $exts);
        }
        return $this->getData('installed_extensions');
    }
}