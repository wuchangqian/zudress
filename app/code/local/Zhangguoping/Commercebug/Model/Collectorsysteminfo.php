<?php 
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Zhangguoping_Commercebug_Model_Collectorsysteminfo extends Zhangguoping_Commercebug_Model_Observingcollector
{
    protected $_items;
    public function collectInformation($observer)
    {
        if(!$this->_isOn($observer))
        {
            return;
        }
        $collection = $this->getCollector();
        $system_info = new stdClass();			
        $system_info->ajax_path = $this->getUrl();
        $this->_items['system_info'] = $system_info;
    }

    /**
    * Returns a base URL for AJAX Requests
    */		
    public function getUrl()
    {
        $shim = $this->getShim();
        $fake = 'fakeactiontotrimoff/'; //not clear if 'commercebug/ajax/' route will return "index" in the URL or not
        $url = str_replace($fake,'',
            Mage::getUrl('commercebug/ajax/'.$fake,
                array('_secure'=>$shim->getModel('core/store')->isCurrentlySecure())));
        if($url[strlen($url)-1] == '/')
        {
            $url = substr($url, 0, strlen($url)-1);
        }
        return $url;
    }
    
    public function addToObjectForJsonRender($json)
    {
        $json->system_info = new stdClass();
        if(is_object($this->_items['system_info']))
        {
            $json->system_info = $this->_items['system_info'];
        }
        return $json;
    }
    
    public function createKeyName()
    {
        return 'systeminfo';
    }
}