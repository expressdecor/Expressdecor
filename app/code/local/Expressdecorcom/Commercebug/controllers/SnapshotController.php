<?php
	class Expressdecorcom_Commercebug_SnapshotController extends Mage_Core_Controller_Front_Action
	{
		public function otherAction()
		{
			try
			{
				$results = Mage::getModel('commercebug/corescan_external')
				->createSnapshot($this->getRequest()->getPost());
				$message = sprintf('Added %d Files',$results->number_added);
				$success = true;
			}
			catch(Exception $e)
			{
				$success = false;
				$message = strip_tags($e->getMessage());
			}								
			$this->outputJson($success, $message);
			// $this->loadLayout();			
// 			$this->renderLayout();
		}
		
		public function coreAction()
		{		
			$name = urldecode($this->getRequest()->getParam('name'));
			if($name)
			{
				try
				{
					$results = Mage::helper('commercebug/corescan')->createSnapshotFromBase($name);
					$message = sprintf('Added %d Files',$results->number_added);
					$success = true;			
				}
				catch(Exception $e)
				{
					$message = strip_tags($e->getMessage());
					$success = false;					
				}
			}
			else
			{
				$message = sprintf('Added %d Files',$results->number_added);			
				$success = false;			
			}
			$this->outputJson($success, $message);
		}
		
		public function outputJson($success, $message)
		{
			echo Mage::getModel('commercebug/ajaxresponse')
			->setSuccess($success)
			->setMessage($message)
			->render();		
		}
	}