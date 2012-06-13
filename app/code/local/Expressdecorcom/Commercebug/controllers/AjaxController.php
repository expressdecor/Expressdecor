<?php
	class Expressdecorcom_Commercebug_AjaxController extends Mage_Core_Controller_Front_Action
	{
		public function testbedAction()
		{
			$helper = Mage::helper('configlint/runner')
			->runLints()
			->report();
		}
				
		public function indexAction()
		{
			$foo = new stdClass();
			$foo->message = 'Hello World';
			$this->endWithJson($foo);
		}

		public function lookupClassAction()
		{
			$helper = Mage::helper('commercebug/classurilookup');
			$class = trim($this->getRequest()->getParam('class'));
			$uri 	= $helper->classToUri($class);
			$type 	= $helper->classToType($class);
			
			$response = new stdClass();
			$response->{'Resolves to URI '} = $uri;
			$response->{'URI [' . $uri . '] in ' . $type . ' context resolves to class'} = 
			$class = Mage::getConfig()->getGroupedClassName($type,$uri);
			$file  = $this->getClassFile($class);
			if($file)
			{
				$class = $class . 
				'<br>' . 
				'<span class="pathinfo"> ' . $file . ' </path>';			
			}
			$response->{'URI [' . $uri . '] in ' . $type . ' context resolves to class'} = $class;
			
			$html = $this->objectToNonSemanticTable($response);
			$html = str_replace('<dd','<dd class="classname"',$html);
			
			$this->endWithHtml($html);
		}

		private function getClassFile($className)
		{
			if(@class_exists($className))
			{
				$r = new ReflectionClass($className);
				return $r->getFileName();		
			}
			return '';
		}
		
		protected function classDecoratedWithPath($class)
		{
			$file  = $this->getClassFile($class);
			if($file)
			{
				$class = $class . 
				'<br>' . 
				'<span class="pathinfo">' . $file . '</path>';			
			}		
			return $class;
		}
		
		public function lookupUriAction()
		{
			$response = new stdClass();
			$uri = trim($this->getRequest()->getParam('uri'));
			
			//need to surpress "could not include" warning
			
			//getGroupedClassName
			
			if($this->isModelContext())
			{
				$response->{'Model Grouped Class Name '} 				 = Mage::getConfig()->getGroupedClassName('model',$uri);
				$response->{"Mage::getModel('$uri') creates a "} 		 = @get_class(Mage::getModel($uri));			
				$response->{"Mage::getResourceModel('$uri') creates a "} = @get_class(Mage::getResourceModel($uri));
			}
			
			if($this->isHelperContext())
			{
				$response->{'Helper Grouped Class Name '} 	= Mage::getConfig()->getGroupedClassName('helper',$uri);			
				$test = @class_exists($response->{'Helper Grouped Class Name '});
				if($test)
				{
					$response->{'Mage::helper(\''.$uri.'\') creates a '} = @get_class(Mage::helper($uri));
				}
				else
				{
					$response->{'Mage::helper(\''.$uri.'\') creates a '} = false;
				}
			}

			if($this->isBlockContext())
			{
				//$response->block 			= @get_class(Mage::getConfig()->getBlockClassName($uri));
				$response->{"Block Grouped Class Name"}		= Mage::getConfig()->getGroupedClassName('block',$uri);			
				$response->{"\$this->getLayout()->createBlock('$uri') creates a "} 	= @get_class($this->getLayout()->createBlock($uri));
			}			
			
			//decorate response with classname
			foreach($response as $key=>$value)
			{
				if($value)
				{
					$response->{$key} = $this->classDecoratedWithPath($value);
				}
			}
			
			//$response->block 			= get_class(Mage::getBlock($uri));
			
			$html = $this->objectToDefinitionList($response);
			$html = $this->objectToNonSemanticTable($response);
			$html = str_replace('<dd','<dd class="classname"',$html);
			
			$this->endWithHtml($html);
		}

		private function isModelContext()
		{
			if('all' == $this->getRequest()->getParam('context') || 
			'model' == $this->getRequest()->getParam('context'))
			{
				return true;		
			}
			return false;		
		}
		
		private function isHelperContext()
		{
			if('all' == $this->getRequest()->getParam('context') || 
			'helper' == $this->getRequest()->getParam('context'))
			{
				return true;		
			}
			return false;			
		}

		private function isBlockContext()
		{
			if('all' == $this->getRequest()->getParam('context') || 
			'block' == $this->getRequest()->getParam('context'))
			{
				return true;		
			}
			return false;		
		}		
		
		protected function objectToNonSemanticTable($object)
		{
			$html = '<table class="tablesorter">';
			$html .= '<thead><tr><th>Label</th><th>Class Information</th></tr></thead>';			
			$html .= '<tbody>';			
			
			$c=0;
			foreach($object as $key=>$value)
			{
				$oddeven = $c % 2 ? 'odd' : 'even';
				$html .= '<tr class="'.$oddeven.'">';
				$html .= '<td> ' . $key . '</td>';
				$value = $value ? $value : 'Could Not Find';
				$html .= '<td> ' . $value . '</td>';
				$html .= '</tr>';				
				$c++;
			}
			$html .= '</tbody>';						
			$html .= '</table>';			
			return $html;		
		}
		protected function objectToDefinitionList($object, $attributes=array())
		{
			$html = '<dl';
			foreach($attributes as $key=>$value)
			{
				$html .= ' ' . $key . '="' . $value .'"';
			}
			$html.='>';
			foreach($object as $key=>$value)
			{
				$html .= '<dt> ' . $key . '</dt>';
				$value = $value ? $value : 'Could Not Find';
				$html .= '<dd> ' . $value . '</dd>';
			}
			$html .= '</dl>';
			
			return $html;
		}
		
		protected function endWithHtml($html)
		{
			header('Content-Type: text/html');
			echo $html;
			exit;
		}
		
		protected function endWithJson($object)
		{
			header('Content-Type: application/json');
			echo Mage::getSingleton('commercebug/jsonbroker')->jsonEncode($object);
			exit;
		}
		
		//this is soooo cheating
		public function clearcacheAction()
		{
			Mage::helper('commercebug/cacheclearer')->clearCache();
// 			require_once('app/code/core/Mage/Adminhtml/controllers/CacheController.php');
// 			$controller = new Mage_Adminhtml_CacheController($this->getRequest(), $this->getResponse());
// 			$controller->flushSystemAction();
			$this->endWithHtml('Cache Cleared');
		}		
	}
