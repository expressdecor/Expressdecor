<?php
/*
 * Magento ArtsOnIt Offline Maintenance Page
 *
 * @category   ArtsOnIt
 * @package    Mage_OfflineMaintenance
 * @copyright  Copyright (c) 2009 ArtsOn.IT (http://www.ArtsOn.it)
 * @author     Calore Luca Erico (l.calore@ArtsOn.it)
  *@licence     www.artson.it/licence/magento/omp
 */
class ArtsOnIT_OfflineMaintenance_Controller_Router_Standard extends Mage_Core_Controller_Varien_Router_Standard
{
     
    public function match(Zend_Controller_Request_Http $request)
    {
		 
		$storeenabled = Mage::getStoreConfig('offlineMaintenance/settings/enabled', $request->getStoreCodeFromPath());
		if ($storeenabled)
		{  
			Mage::getSingleton('core/session', array('name' => 'adminhtml'));
			if (!Mage::getSingleton('admin/session')->isLoggedIn())
			{  
				Mage::getSingleton('core/session', array('name' => 'front'));
				
				$front = $this->getFront();
				$response = $front->getResponse();
			    $response->setHeader('HTTP/1.1','503 Service Temporarily Unavailable');
				$response->setHeader('Status','503 Service Temporarily Unavailable');
				$response->setHeader('Retry-After','5000');
	 
				$response->setBody(html_entity_decode( Mage::getStoreConfig('offlineMaintenance/settings/message', $request->getStoreCodeFromPath()), ENT_QUOTES, "utf-8" )); 			$response->sendHeaders();
				$response->outputBody();
				
				exit;
			}
			else
			{				
				$showreminder = Mage::getStoreConfig('offlineMaintenance/settings/showreminder', $request->getStoreCodeFromPath());
				if ($showreminder)
				{
					$front = $this->getFront();
					$response = $front->getResponse()->appendBody('<div style="height:12px; background:red; color: white; position:relative; width:100%;padding:3px; z-index:100000;text-trasform:capitalize;">Offline</div>');
				}
			}
		}
		return parent::match($request);
        
    }

    
   
}