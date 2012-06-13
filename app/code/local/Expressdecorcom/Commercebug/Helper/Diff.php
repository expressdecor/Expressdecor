<?php
	/**
	* Links in lgpl PEAR diff library
	*/
	class Expressdecorcom_Commercebug_Helper_Diff
	{
		public function __construct()
		{
			$this->requirePear();					
		}
		
		public function requirePear()
		{
			$paths = explode(":",get_include_path());
			$paths[] = realpath(dirname(__FILE__) . '/../vendor/PEAR');
			set_include_path(implode(":",$paths));
			require_once('Text/Diff.php');
			require_once('Text/Diff/Renderer.php');
			require_once('app/code/local/Alanstormdotcom/Commercebug/vendor/PEAR/Text/Diff/Renderer/unified.php');		
		}
		
		public function diff($first, $second, $type='auto',$render='unified')
		{			
			$diff = new Text_Diff('auto',array($first,$second));			
			$class = 'Text_Diff_Renderer_' . $render;
			$renderer = new $class;
			return $renderer->render($diff);
		
		}		
	}