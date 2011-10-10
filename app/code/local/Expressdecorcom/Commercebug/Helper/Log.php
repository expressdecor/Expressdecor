<?php
	class Expressdecorcom_Commercebug_Helper_Log
	{
	    public function log($message, $level=null, $file = '')
	    {	    
			if(Mage::getStoreConfig('commercebug/options/should_log'))
			{
				Mage::Log($message, $level, $file);
			}	    	
	    }	    
	}