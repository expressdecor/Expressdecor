<?php
class Expressdecorcom_Commercebug_Model_Observer
{
	private function getBaseStaticPath()
	{
		return 'app/code/local/Alanstormdotcom/Commercebug/static';
	}
	
	private function getJqueryUiHead()
	{
	
		return $this->getLayout()->createBlock('commercebug/html')
		->setTemplate('head_jquery_ui.phtml')->toHtml();
	}
	
	private function getJqueryUiBodyend()
	{
		return $this->getLayout()->createBlock('commercebug/html')
		->setTemplate('bodyend_jquery_ui.phtml')->toHtml();
	}
	
	private function getJqueryUiTabsHtml()
	{
		return $this->getLayout()->createBlock('commercebug/alltabs')
		->setTemplate('bodystart_jquery_ui_tabs_html.phtml')->toHtml();		
	}
	
	protected function collectSystemInfo()
	{
		//this looks like an old code path that isn't used anymore
		$collection = $this->getCollector();
		$system_info = new stdClass();
		$system_info->ajax_path = Mage::getBaseUrl() . 'commercebug/ajax';
		$collection->saveItem('system_info',$system_info);
	}
	
	private function getCommercebugInitScript()
	{				
		//$this->collectSystemInfo();		
		$collection = $this->getCollector();		
		//in a string to avoid accidently sending a raw javascript
		//command to the browser.  
		$script = ('<script type="text/javascript">
			var commercebug_json = \'' . 
			str_replace("\\","\\\\",$collection->asJson()) . 
			'\';			
		</script>');		
		return $script;	
	}
	
	public function doNotDisplay()
	{
		return (
		!Mage::helper('commercebug')->isModuleOutputEnabled()
		|| !Mage::getStoreConfigFlag('commercebug/options/show_interface')
		|| !Mage::getSingleton(Mage::getStoreConfig('commercebug/options/access_class'))->isOn()
		)	;
		//	
	}
	
	public function addCommercebugInit($observer)
	{
		if($this->doNotDisplay())
		{			
			return;
		}	
		$response = $observer->getResponse();
		$this->checkForSingleWindowMode($response);
		$this->appendToActualHtmlHeadResponse($response,$this->getJqueryUiHead());		
		$this->appendToActualHtmlHeadResponse($response,$this->getJqueryUiBodyend());
		$this->prependToHtmlBody($response, $this->getJqueryUiTabsHtml());
		$this->appendToActualHtmlBodyResponse($response,$this->getCommercebugInitScript());	
	}
	
	protected function checkForSingleWindowMode($response)
	{
		if(!array_key_exists('commercebugSingleWindowMode',$_GET)){
			return;
		}
		$response->setBody('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Commerce Bug</title>
</head>
<body>

</body>
</html>');	
	}
	
	//using string replacment in case their document is poorly formed
	protected function prependToHtmlBody($response, $content)
	{		
		$response->setBody(
			preg_replace('{(</head>\s+?<body.*?>)}i','$1'.$content,$response->getBody())
		);		
	}
	
	//may be problematic, should consider using loadHTML of dom document
	protected function appendToActualHtmlTagResponse($tag,$response, $content)
	{
		$response->setBody(
			str_replace('</'.$tag.'>',$content.'</'.$tag.'>',$response->getBody(false))
		);	
	}
	
	protected function appendToActualHtmlBodyResponse($response, $content)
	{
		return $this->appendToActualHtmlTagResponse('body',$response, $content);
	}

	protected function appendToActualHtmlHeadResponse($response, $content)
	{
		return $this->appendToActualHtmlTagResponse('head',$response, $content);
	}	
	
	private function insertBlockAtEndIfEndExists($block)
	{
		$last_block = $this->getLayout()->getBlock('before_body_end');
		if(is_object($last_block))
		{
			$last_block->append($block);
		}			
	}
		
	private function getLayout()
	{
		return Mage::getSingleton('core/layout');;	
	}
	
	private function getCollector()
	{
		return $collector = Mage::getSingleton('commercebug/collector');	
	}
}