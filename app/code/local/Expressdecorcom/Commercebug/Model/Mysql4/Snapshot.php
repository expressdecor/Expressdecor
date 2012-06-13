<?php
	class Expressdecorcom_Commercebug_Model_Mysql4_Snapshot extends Mage_Core_Model_Mysql4_Abstract
	{
		protected function _construct()
		{
			$this->_init('commercebug/snapshot', 'snapshot_id');
		}  	
		
		public function deleteSnapshots($snapshot_id)
		{
			$this->_getWriteAdapter()->delete(
				$this->getMainTable(),
				$this->_getWriteAdapter()->quoteInto('snapshot_name_id=?', $snapshot_id)
			);
		}
	}