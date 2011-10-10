<?php
	class Expressdecorcom_Commercebug_Model_Corescan_External extends Varien_Object
	{
		/**
		* Create the Snapshot
		*
		* param $info array('corescan_library_folder'=>...,'corescan_module_folder'=>...,'corescan_snapshot_name'=>...,);
		*/	
		public function createSnapshot($info)
		{		
			extract($info);			
			$this->_checkFolders($corescan_library_folder, $corescan_module_folder);			
			$root = $this->_getSharedRoot($corescan_library_folder, $corescan_module_folder);			
			//get the root prefix
			//create snapshot with helper
			return Mage::helper('commercebug/corescan')
			->createSnapshotFromFiles($corescan_snapshot_name,$corescan_library_folder,$corescan_module_folder,$root);
		}
		
		protected function _checkFolders($corescan_library_folder, $corescan_module_folder)
		{
			//check for folders
			foreach(array($corescan_library_folder, $corescan_module_folder) as $folder)
			{
				if(!is_dir($folder))
				{
					throw new Exception('Could not find path [' . strip_tags($folder). '] on this server');
				}
			}		
		}
		
		protected function _getSharedRoot($corescan_library_folder, $corescan_module_folder)
		{
			$max_length = strlen($corescan_library_folder) > strlen($corescan_module_folder) 
				? strlen($corescan_library_folder)
				: strlen($corescan_module_folder);

			$shared = array();				
			for($i=0;$i<$max_length;$i++)
			{
				if($corescan_library_folder[$i] == $corescan_module_folder[$i])
				{
					$shared[] = $corescan_library_folder[$i];
				}
				else
				{
					break;
				}
			}
			
			$root = implode('',$shared);
			if(!$root || !is_dir($root))
			{
				throw new Exception('Library and core modules must have common parent folder');
			}
			
			return $root;
		}
		
	}
	