<?php
	class Expressdecorcom_Commercebug_Helper_Corescan extends Mage_Core_Helper_Abstract
	{
		protected $_helperDiff;
		public function getAppRoot()
		{
			return Mage::getRoot();
		}
		
		protected function _getDiffHelper()
		{
			if(!$this->_helperDiff)
			{
				$this->_helperDiff = Mage::helper('commercebug/diff');
			}
			return $this->_helperDiff;
		}
		
		protected function _runDiffForRow($i,$diffs)
		{
			$helper_diff 		= $this->_getDiffHelper();
			$path				= $this->getAppRoot() . '/../' . $i['file'];
			$hash_filesystem 	= self::hashWithRemovedLineendings($path);
			$diff			= new Varien_Object();	
			$diff->file		= $i['file'];			
			if($hash_filesystem != $i['hash'])
			{
				
				if(file_exists($path))
				{
					$diff->output 	= $helper_diff->diff(
					preg_split('{[\r\n]}',$i['contents'],null,PREG_SPLIT_NO_EMPTY),
					preg_split('{[\r\n]}',file_get_contents($path),null,PREG_SPLIT_NO_EMPTY)
					);
				}
				else
				{
					$diff->output   = 'Could not find ' . $path;
				}
				$diffs[]		= $diff;
			}		
		}
		
		public function diffSnapshot($snapshot_name)
		{						
			$snapshot_name_id		= Mage::getModel('commercebug/snapshot_name')
			->getCollection()->addFieldToFilter('snapshot_name', $snapshot_name)
			->getFirstItem()->getSnapshotNameId();
			if(!$snapshot_name_id)
			{
				throw new Exception("No Snapshot found [" . strip_tags($snapshot_name) . "]");
			}
			$q 						= Mage::getModel('commercebug/snapshot')
			->getCollection()
			->addFieldToFilter('snapshot_name_id',$snapshot_name_id)
			->getSelect();			
			
			$diffs 					= new ArrayObject();
			$c=0;
			foreach($q->query() as $row)
			{								
				$this->_runDiffForRow($row,$diffs);
				$c++;		
				Mage::Log("Compared Filed " . $row['file']);
// 				var_dump($c);
// 				flush();
			}	
			
			$results				= new Varien_Object();
			$results->setNumberOfFilesScanned($c);
			$results->setDiffs($diffs);
			return $results;
		}
		
		public function getPathsToScan()
		{
			return array(
				realpath($this->getAppRoot() . '/../lib'),
				realpath($this->getAppRoot() . '/code/core'),
			);
		}
		
		public function createSnapshotFromFiles($name,$lib,$core,$root=false)
		{
			$name = strip_tags($name);
			$files = $this->makeFileList(array($lib, $core));
			return $this->createSnapshot($name,$files,$root);
		}
		
		public function createSnapshotFromBase($name,$root=false)
		{
			$name = strip_tags($name);
			$files = $this->makeFileList($this->getPathsToScan());
			return $this->createSnapshot($name,$files,$root);
		}
		
		protected function getSnapshotNameId($name)
		{
			$snapshot_name_id = Mage::getModel('commercebug/snapshot_name')->getCollection()
			->addFieldToFilter('snapshot_name', $name)->getFirstItem()->getSnapshotNameId();
			if(!$snapshot_name_id)
			{
				return Mage::getModel('commercebug/snapshot_name')
				->setSnapshotName($name)
				->save()
				->getSnapshotNameId();
			}
			return $snapshot_name_id;
		}
		
		protected function createSnapshot($name,$files,$root)
		{
			$name = strip_tags($name);		
			$snapshot_name_id = $this->getSnapshotNameId($name);
			Mage::getModel('commercebug/snapshot')->deleteSnapshots($snapshot_name_id);								
			// $files = $this->makeFileList();
			foreach($files as $file)
			{
				if(!is_dir($file) && file_exists($file))
				{
					Mage::Log("Adding File: " . $file);				
					$snapshot = Mage::getModel('commercebug/snapshot');				
					$snapshot->setSnapshotNameId($snapshot_name_id)
					->setHash(self::hashWithRemovedLineendings($file))
					->setFile($this->stripPathPrefix($file,$root))
					->setContents(preg_replace('%[\r\n]{1,2}$%',"\n",file_get_contents($file)))
					->save();
				}
				else
				{
					Mage::Log("Skipped Adding File: " . $file);				
				}
			}
			
			$result = new stdClass();
			$result->number_added = count($files);
			return $result;
		}
		
		protected function stripPathPrefix($file,$root)
		{			
			$root = $root ? $root : ($this->getAppRoot() . '/../');
			return str_replace(realpath($root),'',$file);
		}
		
		protected function makeFileList($paths_to_scan)
		{
			$parts = array();			
			foreach($paths_to_scan as $dir)
			{
				$tmp 	= $this->recursiveGlob($dir);
				$parts 	= array_merge($parts, $tmp);
			}					
			return $parts;
		}
		
		static protected function hashWithRemovedLineendings($file)
		{
			if(file_exists($file))
			{
				return md5(preg_replace('%[\r\n]%','',file_get_contents($file)));
			}
			return md5('NOFILE');
		}
		
		static protected function recursiveGlob($path, $parts=false)
		{
			$parts = $parts ? $parts : array();
			$path .= '/*';
			foreach(glob($path) as $file)
			{
				if($file == '.' || $file == '..')
				{
					continue;
				}
				$parts[] = $file;
				if(is_dir($file))
				{
					$parts = self::recursiveGlob($file, $parts);
				}
			}		
			return $parts;
		}
		
	}