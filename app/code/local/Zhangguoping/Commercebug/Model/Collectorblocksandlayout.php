<?php 
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Zhangguoping_Commercebug_Model_Collectorblocksandlayout extends Zhangguoping_Commercebug_Model_Observingcollector
{
    protected $_blocks=array();
    public function collectInformation($observer)
    {
        if(!$this->_isOn($observer))
        {
            return;
        }

        $block = $observer->getBlock();
        $collector = $this->getCollector();
        $this->_blocks[] = $block;
    }
    
    protected function _getTemplateNameFromBlock($block)
    {
        $file = $block->getTemplateFile();
        $file = !preg_match('{/$}i',$file) ? $file : false;
        $file = $file ? $file : $block->getTemplate();			
        return $file;
    }
    
    public function addToObjectForJsonRender($json)
    {		
        $json->blocks = array();
        $json->blockFiles = array();
        foreach($this->_blocks as $block)
        {
            $class 		= get_class($block);
            if(strpos($class,'Zhangguoping_Commercebug') === 0)
            {
                continue;
            }
            $template 	= $this->_getTemplateNameFromBlock($block);
            $key 		= $class . '::' . $template;
            if(!array_key_exists($key,$json->blocks))
            {
                $json->blocks[$key] = 0;
            }	
            $json->blocks[$key]++;
            $json->blockFiles[$key] = $this->getClassFile($class);				
        }		
        
        $json->layout = new stdClass();			
        if(is_object($this->getLayout()))
        {
            $json->layout->handles = $this->getLayout()->getUpdate()->getHandles();
        }
        
        $json->design_paths = $this->getShim()->getModel('commercebug/designpathinfo')->getData();
        $json->xml_file     = $this->getXmlFiles();
        return $json;
    }

    public function createKeyName()
    {
        return 'blocksandlayout';
    }
}