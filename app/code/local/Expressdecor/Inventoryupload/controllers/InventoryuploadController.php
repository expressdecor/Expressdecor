<?php 
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@expressdecor.com so we can send you a copy immediately.
 *
 * @author Alex Lukyanov
 * @copyright   Copyright (c) 2013 ExpressDecor. (http://www.expressdecor.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Created: May 6, 2013
 *
 */
class Expressdecor_Inventoryupload_InventoryuploadController extends Mage_Adminhtml_Controller_Action {
	
	protected function _construct()
	{
		// Define module dependent translate
		$this->setUsedModuleName('Expressdecor_Inventoryupload');
	}
	
	/**
	 * Check for is allowed
	 *
	 * @return boolean
	 */
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('expressdecor/inventoryupload');
	}
	
	
	public function indexAction()
	{			
		$this->loadLayout();	 		
		$block = $this->getLayout()->createBlock('inventoryupload/inventoryformcontainer','inventoryform');
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();		 
	}
	
	public function saveAction()
	{	 
		if(isset($_FILES['filecsv']['name']) and (file_exists($_FILES['filecsv']['tmp_name']))) {
			try {
				$uploader = new Varien_File_Uploader('filecsv');
				$uploader->setAllowedExtensions(array('csv'));
				$uploader->setAllowRenameFiles(true);
				$path = Mage::getBaseDir('var') . DS.'import'.DS;				 			 
				$filename=$uploader->getCorrectFileName($_FILES['filecsv']['name']);
				if (file_exists($path.$filename))
					unlink ($path.$filename);
				$uploader->save($path, $filename);								 
				$this->_getHelper()->processfile($path.$filename);
				Mage::getSingleton('core/session')->addSuccess("File was succefully processed.");				
			}catch(Exception $e) { 				 
 				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
  			}
		} else { 
			Mage::getSingleton('adminhtml/session')->addError("File wasn't uploaded");			 
		}
		$this->_redirect('*/*/');
	}	
	
    /**
     * Retrieve base admihtml helper
     *
     * @return Mage_Adminhtml_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('inventoryupload');
    }
	
	
}