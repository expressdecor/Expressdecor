<?php 
	class Expressdecorcom_Commercebug_Model_Collectorblocksandlayout extends Expressdecorcom_Commercebug_Model_Observingcollector
	{
		protected $_blocks=array();
		public function collectInformation($observer)
		{
			if(!Mage::getSingleton(Mage::getStoreConfig('commercebug/options/access_class'))->isOn())
			{
				return;
			}		
			$block = $observer->getBlock();
			$collector = $this->getCollector();
			$this->_blocks[] = $block;
		}
		
		public function addToObjectForJsonRender($json)
		{		
			$json->blocks = array();
			$json->blockFiles = array();
			foreach($this->_blocks as $block)
			{
				$class 		= get_class($block);
				$template 	= $block->getTemplate();
				$key 		= $class . '::' . $template;
				if(!array_key_exists($key,$json->blocks))
				{
					$json->blocks[$key] = 0;
				}	
				$json->blocks[$key]++;
				$json->blockFiles[$key] = $this->getClassFile($class);				
			}		
			
			$json->layout = new stdClass();			
			if(is_object($this->getLayout()))
			{
				$json->layout->handles = $this->getLayout()->getUpdate()->getHandles();
			}
			
			return $json;
		}
		
		public function createKeyName()
		{
			return 'blocksandlayout';
		}
	}