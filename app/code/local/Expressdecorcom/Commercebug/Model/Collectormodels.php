<?php 
	class Expressdecorcom_Commercebug_Model_Collectormodels extends Expressdecorcom_Commercebug_Model_Observingcollector
	{
		protected $_models=array();
		public function collectInformation($observer)
		{
			if(!Mage::getSingleton(Mage::getStoreConfig('commercebug/options/access_class'))->isOn())
			{
				return;
			}		
			$collector = $this->getCollector();
			$this->_models[] = $observer->getEvent()->getObject();
		}

		public function addToObjectForJsonRender($json)
		{
			$json->models						= array();
			$json->modelFiles					= array();
			foreach($this->_models as $model)
			{
				$class = get_class($model);
				if(!array_key_exists($class,$json->models))
				{
					$json->models[$class] = 0;
				}
				$json->models[$class]++;
				$json->modelFiles[$class] = $this->getClassFile($class);
				
			}
						
			return $json;
		}
		
		public function createKeyName()
		{
			return 'models';
		}
	}
