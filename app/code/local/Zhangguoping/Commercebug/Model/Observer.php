<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Zhangguoping_Commercebug_Model_Observer
{
    const TEMPLATE_HINTS_ON = 'TEMPLATE_HINTS_ON';
    const BLOCK_HINTS_ON    = 'BLOCK_HINTS_ON';
    const MAGE_LOGGING_ON   = 'MAGE_LOGGING_ON';
    const CB_LOGGING_ON     = 'CB_LOGGING_ON';
    
	private function getBaseStaticPath()
	{
		return 'app/code/local/Alanstormdotcom/Commercebug/static';
	}
	
	private function getJqueryUiHead()
	{
	
		return $this->getShim()->createBlock('commercebug/jquery_head')
		->toHtml();
	}
	
	private function getJqueryUiBodyend()
	{
		return $this->getShim()->createBlock('commercebug/jquery_bodyend')
		->toHtml();
	}
	
	private function getJqueryUiTabsHtml()
	{
		return $this->getShim()->createBlock('commercebug/alltabs')
		->toHtml();		
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
		$script = ('<script type="text/javascript">//<![CDATA[
			var commercebug_json = \'' . 
			str_replace("\\","\\\\",$collection->asJson()) . 
			'\';			
		//]]></script>');		
		return $script;	
	}
	
	public function doNotDisplay()
	{
	    $shim = $this->getShim();
	    
		return (
		!$shim->helper('commercebug')->isModuleOutputEnabled()
		|| !Mage::getStoreConfigFlag('commercebug/options/show_interface')
		|| !Mage::getSingleton('commercebug/ison')->isOn()
		)	;
		//	
	}
	
	public function addCommercebugInit($observer)
	{		
		if($this->doNotDisplay())
		{			
			return;
		}	

        $shim = $this->getShim();
        //capture all events up to the response before
        //Mage::getSingleton('commercebug/events')->capture();
        $this->getShim()->getSingleton('commercebug/events')->capture();
        $this->getShim()->getSingleton('commercebug/files')->capture();
        $this->getShim()->getSingleton('commercebug/graphviz')->capture();
        
        
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
			preg_replace('{(</head>\s*?<body.*?>)}i','$1'.$content,$response->getBody())
		);		
	}
	
	//may be problematic, should consider using loadHTML of dom document
	protected function appendToActualHtmlTagResponse($tag,$response, $content)
	{
		$response->setBody(
			str_replace('</'.$tag.'>',$content.'</'.$tag.'>',$response->getBody(false))
		);	
// 		$response->appendBody(
// 			str_replace('</'.$tag.'>',$content.'</'.$tag.'>',$response->getBody(false))
// 		);	

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
	    $shim = $this->getShim();
		return $shim->getSingleton('core/layout');;	
	}
	
	private function getCollector()
	{
	    $shim = $this->getShim();
		return $collector = $shim->getSingleton('commercebug/collector');
	}
	
	public function getShim()
	{
        $shim = Zhangguoping_Commercebug_Model_Shim::getInstance();
        return $shim;
	}	
	
	public function jiggerConfig($observer)
	{
    	if(!Mage::getSingleton(Mage::getStoreConfig('commercebug/options/access_class'))->isOn())
    	{
    	    return;
    	}
        
        $this->_jiggerLogging();
        
	    $config = Mage::getConfig();
        $store  = Mage::app()->getStore();
        $code   = $store->getCode();
        
        //$cache = Mage::getModel('core/cache');
        $cache = $this->_getCacheObject();
        $c = $cache->load(Zhangguoping_Commercebug_Model_Observer::TEMPLATE_HINTS_ON);        
        if($c !== 'on' && $c !== 'off')
        {
            return;
        }
        $value = $c == 'on' ? '1' : '0';        
        $path = 'dev/debug/template_hints';
        if(defined("Mage_Core_Block_Template::XML_PATH_DEBUG_TEMPLATE_HINTS"))
        {
            $path = Mage_Core_Block_Template::XML_PATH_DEBUG_TEMPLATE_HINTS;
        }
        
	    $config_path = "stores/$code/" . $path;
	    $config->setNode($config_path, $value);
	    
	    $c = $cache->load(Zhangguoping_Commercebug_Model_Observer::BLOCK_HINTS_ON);        
        if($c !== 'on' && $c !== 'off')
        {
            return;
        }	    
        $value = $c == 'on' ? '1' : '0';        
        $path = 'dev/debug/template_hints_blocks';
        if(defined("Mage_Core_Block_Template::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS"))
        {
            $path = Mage_Core_Block_Template::XML_PATH_DEBUG_TEMPLATE_HINTS_BLOCKS;
        }
        $config_path = "stores/$code/" . $path;
	    $config->setNode($config_path, $value);	             
	}
	
	protected function _jiggerLogging()
	{
	    $config = Mage::getConfig();
        $store  = Mage::app()->getStore();
        $code   = $store->getCode();        
        //$cache = Mage::getModel('core/cache');
        $cache = $this->_getCacheObject();

	    $c = $cache->load(Zhangguoping_Commercebug_Model_Observer::MAGE_LOGGING_ON);        
        if(!($c !== 'on' && $c !== 'off'))
        {
            $value = $c == 'on' ? '1' : '0';        
            $config_path = "stores/$code/" . 'dev/log/active';
            $config->setNode($config_path, $value);              
        }	    
        
	    $c = $cache->load(Zhangguoping_Commercebug_Model_Observer::CB_LOGGING_ON);        
        if(!($c !== 'on' && $c !== 'off'))
        {
            $value = $c == 'on' ? '1' : '0';        
            $config_path = "stores/$code/" . 'commercebug/options/should_log';
            $config->setNode($config_path, $value);              
        }	        
// 	    Mage::Log($config_path, $value);
	    
// 	    $c = $cache->load(Zhangguoping_Commercebug_Model_Observer::CB_LOGGING_ON);        
//         if($c !== 'on' && $c !== 'off')
//         {
//             return;
//         }	    
//         $value = $c == 'on' ? '1' : '0';        
//         $config_path = "stores/$code/" . 'commercebug/options/should_log';
// 	    $config->setNode($config_path, $value); 	
	}
	
	protected function _getCacheObject()
	{
	    return Mage::app()->getCache();
	    return Mage::getModel('core/cache');
	}
}