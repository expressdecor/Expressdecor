<?php
	class Expressdecorcom_Commercebug_Model_Corescan extends Varien_Object
	{
		public function defaultScan()
		{			
			$this->scanBySnapshotName(Mage::getVersion());					
		}
		
		public function scanBySnapshotName($snapshot_name)
		{
			$h		= Mage::helper('commercebug/corescan');			
			$this->setDiffResults($h->diffSnapshot($snapshot_name));		
		}
		
		public function scanBySnapshotNameId($snapshot_id)
		{
			$this->scanBySnapshotName(Mage::getModel('commercebug/snapshot_name')
			->load($snapshot_id)->getSnapshotName());
		}
		
	}