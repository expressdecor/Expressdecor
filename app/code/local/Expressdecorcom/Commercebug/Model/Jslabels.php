<?php
	class Expressdecorcom_Commercebug_Model_Jslabels extends Mage_Core_Model_Abstract
	{	
		public function addTableLabelsToJson($json)
		{
			$json->labels = new stdClass();
			foreach($this->getLabels() as $name=>$label)
			{
				$json->labels->{$name} = $label;
			}			
			return $json;
		}
		
		public function getLabels()
		{
			return array(
			'models'=>array(
				'column_1'=>$this->__('Model Name'),
				'column_2'=>$this->__('Times Instantiated'),
			),
			'blocks'=>array(
				'column_1'=>$this->__('Block Name'),
				'column_2'=>$this->__('Times Instantiated'),
				'column_3'=>$this->__('With Template'),
			),
			'layouts'=>array(
				'column_1'				=>$this->__('Handles for this Request'),
				'view_page_layout'		=>$this->__('View Page Layout:'),
				'view_package_layout'	=>$this->__('View Package Layout:'),				
				'xml'					=>$this->__('XML'),				
				'text'					=>$this->__('Text'),				
			),			
			);			
		}
		
		protected function __($label)
		{
			return Mage::helper('commercebug')->__($label);
		}
	}