<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/

class Zhangguoping_Commercebug_Helper_Log
{
    public function log($message, $level=null, $file = '')
    {	    
        if(Mage::getStoreConfig('commercebug/options/should_log'))
        {
            Mage::Log($message, $level, $file);
        }	    	
    }	    
    
    public function format($thing)
    {
        $helper = Mage::helper('commercebug/formatlog_allsimple');
        if($helper)
        {
            return $helper->format($thing);
        }
        Mage::Log(sprintf('Could not instantiate helper class: %s',$alias));
        //return;    	
        #return __CLASS__ . 'Serialized:' . $thing;	    	
    }
    
    public function getShim()
    {
        $shim = Zhangguoping_Commercebug_Model_Shim::getInstance();
        return $shim;
    }	    
}