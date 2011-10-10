<?php
	class Expressdecorcom_Commercebug_Model_Ison extends Varien_Object
	{
		/**
		* Commerce Bug calls this methed to determine if it should
		* collect its information and render the debugging interface.
		* This can be used to provide whatever level of access control
		* an end-system-user wants
		* @return boolean if return true, Commerce Bug will collect its information and render.  If false, commercebug will skip
		*/	
		public function isOn()
		{
			return true;
		}
	}