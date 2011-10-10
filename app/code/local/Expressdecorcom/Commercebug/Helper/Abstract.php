<?php
	abstract class Expressdecorcom_Commercebug_Helper_Abstract extends Mage_Core_Helper_Data
	{
		public function isModuleOutputEnabled($moduleName = null)
        {        	
	        if(is_callable(array(Mage::helper('core'),'isModuleOutputEnabled')))
            {
                return parent::isModuleOutputEnabled();
            }
                         
            if ($moduleName === null) {
                $moduleName = $this->_getModuleName();
            }
            if (Mage::getStoreConfigFlag('advanced/modules_disable_output/' . $moduleName)) {
                return false;
            }
            return true;
        }        	
	}