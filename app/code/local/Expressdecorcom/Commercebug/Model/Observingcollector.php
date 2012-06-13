<?php
	abstract class Expressdecorcom_Commercebug_Model_Observingcollector extends Varien_Object
	{

		/**
		* Entry point
		*/	
		abstract public function collectInformation($observer);
		
		/**
		* Not using this yet, but may in future
		*/		
		abstract public function createKeyName();

		/**
		* Automatically passed top level stdClass object, client programmer
		* should populate with whatever they want
		*/		
		abstract public function addToObjectForJsonRender($parent_object);		
		
		protected function getLayout()
		{
			return Mage::getSingleton('core/layout');;	
		}
		
		protected function getCollector()
		{
			return $collector = Mage::getSingleton('commercebug/collector')->registerSingleCollector($this);	
			
		}
	
		protected function getClassFile($className)
		{
			$r = new ReflectionClass($className);
			return $r->getFileName();		
		}
	
	}