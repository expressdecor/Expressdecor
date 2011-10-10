<?php
	class Expressdecorcom_Commercebug_Block_Html extends Mage_Core_Block_Template
	{
	
		public function __construct()
		{
			//can't do this, the scriptpath is set in the actual render view method
			//$this->setScriptPath(dirname(__FILE__) . '/../static');
		}
		
		
	    public function fetchView($fileName)			    
	    {	    	
	    	//ignores file name, just uses a simple include with template name
			$this->setScriptPath(dirname(__FILE__) . '/../static');	    
	    	return parent::fetchView($this->getTemplate());
	    }
	    
	    public function getPathSkin()
	    {
	    	$base_skin = Mage::getBaseUrl('skin');
	    	$base_skin = preg_replace('{/$}','',$base_skin);
	    	$url = str_replace('{{base_skin}}',$base_skin,Mage::getStoreConfig('commercebug/options/path_skin'));
	    	//legacy, add root path if it's not there AND we're not an http url
	    	
	    	if( (strpos($url, $base_skin) !== 0)  && $url[0] != '/' )
	    	{
	    		$url = '/' . $url;
	    	}
	    	return $url;
	    }
	 
	 	const UPDATE_URL = 'http://commercebug.alanstorm.com/index.php/version?callback=?';
	 	public function getUpdateUrl()
	 	{
	 		return self::UPDATE_URL;
	 	}	 		 	


		//86400 = 1 day	 	
	 	const UPDATE_CHECK_RATE_IN_SECONDS = 86400;
	 	public function getCheckForUpdatesFlag()
	 	{			
	 		$last_time = Mage::getSingleton('commercebug/jsonbroker')->jsonDecode(Mage::getStoreConfig('commercebug/options/update_last_check'));
	 		$last_time = $last_time->date;	 			
	 		if((strToTime($last_time) + self::UPDATE_CHECK_RATE_IN_SECONDS) > time())
	 		{
	 			return false;
	 		}	 		
	 		
	 		//if we're still here, check teh config flag
	 		return Mage::getStoreConfig('commercebug/options/check_for_updates');
	 	}

		public function fetchUseKeyboardShortcuts()
		{
	 		return Mage::getStoreConfig('commercebug/options/keyboard_shortcuts');		
		}
		public function calculateNextUpdateCheck()
		{
			$last_time = Mage::getModel('commercebug/jsonbroker')->jsonDecode(Mage::getStoreConfig('commercebug/options/update_last_check'));		
			return date(DATE_RFC822, strToTime($last_time->date)+self::UPDATE_CHECK_RATE_IN_SECONDS);
		}
		public function getLastUpdateCheck()
		{
			$last_time = Mage::getModel('commercebug/jsonbroker')->jsonDecode(Mage::getStoreConfig('commercebug/options/update_last_check'));		
			return date(DATE_RFC822, strToTime($last_time->date));		
		}
	 	public function resetLastUpdated()
	 	{
	 		$object				= new StdClass();
	 		$object->date		= date(DATE_RFC822);	 		
	 		
			$groups_value 		= array();			
			$groups_value['options']['fields']['update_last_check']['value'] = Mage::getModel('commercebug/jsonbroker')->jsonEncode($object);
			$model = Mage::getModel('adminhtml/config_data')
				->setSection('commercebug')
				->setWebsite(null)
				->setStore(null)
				->setGroups($groups_value)
				->save();
				
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();				
	 	}
	 	
	 	public function fetchResultsFromLastUpdateCheck()
	 	{
			return Mage::getStoreConfig('commercebug/options/update_last_check');	 	
	 	}
	 	
	 	public function includeStatic($path)
	 	{	 		
	 		ob_start();
	 		include('app/code/local/Alanstormdotcom/Commercebug/static/' . $path);
	 		return ob_get_clean();
	 	}
	 	
	 	public function getLayout()
	 	{
	 		return Mage::getSingleton('core/layout');
	 	}
	 	
// 	 	public function __()
// 	 	{
// 	 		$result = parent::__();
// 	 		return '?' . $result . '?';
// 	 	}
	}