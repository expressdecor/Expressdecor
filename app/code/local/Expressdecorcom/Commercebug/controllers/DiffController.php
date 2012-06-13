<?php
	class Expressdecorcom_Commercebug_DiffController extends Mage_Core_Controller_Front_Action
	{		
		protected function _initDiffLayout()
		{
			$block = $this->getLayout()->createBlock('commercebug/corescan');			
			$this->getLayout()->getBlock('content')->append($block);
			$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');		
		}
		
		public function nameAction()
		{		
			Mage::getSingleton('commercebug/corescan')->scanBySnapshotName($this->getRequest()->getParam('name'));						
			$this->loadLayout();
			$this->_initDiffLayout();
			$this->renderLayout();
		}
		
		public function bynameidAction()
		{
			Mage::getSingleton('commercebug/corescan')->scanBySnapshotNameId($this->getRequest()->getParam('name'));						
			$this->loadLayout();
			$this->_initDiffLayout();
			$this->renderLayout();		
		}
	}