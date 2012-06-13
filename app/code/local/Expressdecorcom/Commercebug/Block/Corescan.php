<?php
	class Expressdecorcom_Commercebug_Block_Corescan extends Expressdecorcom_Commercebug_Block_Html
	{
		public function __construct()
		{
			$this->setTemplate('corescan/results.phtml');
		}
		
		public function getDiffResults()
		{
			return Mage::getSingleton('commercebug/corescan')->getDiffResults();
		}
		
		public function colorCodeDiff($text)
		{
			$text = preg_replace('%^(\+.+?$)%m','<span style="color:#fff;background-color:green">$1</span>',$text);
			$text = preg_replace('%^(\-.+?$)%m','<span style="color:#fff;background-color:red">$1</span>',$text);
			return $text;
		}
		
	}