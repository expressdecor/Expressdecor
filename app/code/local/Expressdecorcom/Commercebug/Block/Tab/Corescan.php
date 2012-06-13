<?php
	class Expressdecorcom_Commercebug_Block_Tab_Corescan extends Expressdecorcom_Commercebug_Block_Html
	{
		protected $_snapshotNames;
		public function __construct()
		{						
			$this->setTemplate('tabs/ascommercebug_corescan.phtml');
		}		
		
		public function getMageVersion()
		{
			return Mage::getVersion();
		}

		protected function getSnapshotNames()
		{
			if(!$this->_snapshotNames)
			{
				$this->_snapshotNames = Mage::getModel('commercebug/snapshot_name')->getCollection();
			}
			return $this->_snapshotNames;
		}
		
		protected function getHelperJson()
		{
			$json = new stdClass();
			$json->url_diff 			= $this->getUrl('commercebug/diff/bynameid');
			$json->url_snapshot_core 	= $this->getUrl('commercebug/snapshot/core');
			//$json->url_snapshot_other = $this->getUrl('commercebug/snapshot/other');
			return Mage::getSingleton('commercebug/jsonbroker')->jsonEncode($json);
		}
		
		protected function needsInstallSnapshotLink()
		{
			foreach($this->getSnapshotNames() as $snapshot)
			{
				if($snapshot->getSnapshotName() == $this->getMageVersion())
				{
					return false;
				}
			}
			return true;
		}
	}