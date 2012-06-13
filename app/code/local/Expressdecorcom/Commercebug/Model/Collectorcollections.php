<?php 
	class Expressdecorcom_Commercebug_Model_Collectorcollections extends Expressdecorcom_Commercebug_Model_Observingcollector
	{
		protected $_collections=array();
		public function collectInformation($observer)
		{
			$collector = $this->getCollector();
			$this->_collections[] = $observer->getEvent()->getCollection();			
		}
		
		public function addToObjectForJsonRender($json)
		{
			$json->collections						= array();
			$json->collectionFiles					= array();
			$json->collectionModels					= array();
			foreach($this->_collections as $model)
			{
				$class = get_class($model);
				if(!array_key_exists($class,$json->collections))
				{
					$json->collections[$class] = 0;
				}
				$json->collections[$class]++;
				$json->collectionFiles[$class] = $this->getClassFile($class);
				$json->collectionModels[$class] = $this->_getModelNameFromModel($model);
			}					
			return $json;
		}
		
protected function _getModelNameFromModel($model)
{
    if(method_exists($model, 'getModelName'))
    {
        return $model->getModelName();
    }
    if(is_object($model->getResource()) && method_exists($model->getResource(), 'getEntityType'))
    {
        return $model->getResource()->getEntityType()->getEntityModel();
    }
    if(is_object($model->getResource()))
    {
        return 'Method getEntityType not found on ' . get_class($model->getResource());
    }        
    return 'UNKNOWN';
}		
		public function createKeyName()
		{
			return 'collections';
		}
	}
