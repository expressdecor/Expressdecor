<?php
	/**
	* Laying groundwork in-case that "no json functions" install turns into 
	* a real future problem.  Accepts 5.3 args, but doesn't use them. 
	*/
	class Expressdecorcom_Commercebug_Model_Jsonbroker extends Varien_Object
	{
		public function jsonEncode($value, $options=0)
		{
			return json_encode($value);
		}
		
		public function jsonDecode($json,$assoc=false,$depth=512,$options=0)
		{
			return json_decode($json);
		}
	}