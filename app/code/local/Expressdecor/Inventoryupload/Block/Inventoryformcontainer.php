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
class Expressdecor_Inventoryupload_Block_Inventoryformcontainer extends Mage_Adminhtml_Block_Widget_Form_Container{
		
	
	public function __construct()
	{		 
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'inventoryupload';
		$this->_controller = 'inventoryupload';
		$this->_mode='';
		$this->_updateButton('save', 'label', Mage::helper('inventoryupload')->__('Save And Apply'));
		$this->_updateButton('save', 'onclick', 'inventoryupload_form.submit();');	 
	}
	
	public function getHeaderText()
	{
		return Mage::helper('inventoryupload')->__('Inventory Upload');
	}
	
	protected function _prepareLayout()
	{ 
		if ($this->_blockGroup && $this->_controller) {
			$this->setChild('form', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_controller . '_' . $this->_mode . 'form'));
		}
		return  Mage_Adminhtml_Block_Widget_Container::_prepareLayout();
	}
	
	
	}