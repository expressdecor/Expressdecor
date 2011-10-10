<?php
	class Expressdecorcom_Commercebug_Block_Alltabs extends Expressdecorcom_Commercebug_Block_Html
	{
		protected $_config;
		public function __construct()
		{
		}
				
		protected function getConfig()
		{
			if(!$this->_config)
			{
				$this->_config = Mage::getConfig()->loadModulesConfiguration('commercebug.xml')->getNode('tabs');
			}
			
			return $this->_config;
		}
		
		public function getTabIdPairs()
		{
			$tab_id_pairs = array();
			foreach($this->getConfig()->xpath('*') as $node)
			{
				$tab_id_pairs[$node->getName()] = (string) $node->title;
			}		
			return $tab_id_pairs;
		}
		
		public function getTabIdAndHtmlPairs()
		{			
			$tab_html_pairs = array();
			foreach($this->getConfig()->xpath('*') as $node)
			{
				$tab_html_pairs[$node->getName()] = $this->getLayout()->createBlock(
				(string) $node->block
				)->toHtml();
			}		
			return $tab_html_pairs;
		}
		
	}