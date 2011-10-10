<?php
class Brandammo_Pronav_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/pronav?id=15 
    	 *  or
    	 * http://site.com/pronav/id/15 	
    	 */
    	/* 
		$pronav_id = $this->getRequest()->getParam('id');

  		if($pronav_id != null && $pronav_id != '')	{
			$pronav = Mage::getModel('pronav/pronav')->load($pronav_id)->getData();
		} else {
			$pronav = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($pronav == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$pronavTable = $resource->getTableName('pronav');
			
			$select = $read->select()
			   ->from($pronavTable,array('pronav_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$pronav = $read->fetchRow($select);
		}
		Mage::register('pronav', $pronav);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}