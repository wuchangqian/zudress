<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
abstract class Zhangguoping_Commercebug_Model_Observingcollector extends Varien_Object
{
    protected $_storeIsSet=false;
    /**
    * Entry point
    */	
    abstract public function collectInformation($observer);
    
    /**
    * Not using this yet, but may in future
    */		
    abstract public function createKeyName();

    /**
    * Automatically passed top level stdClass object, client programmer
    * should populate with whatever they want
    */		
    abstract public function addToObjectForJsonRender($parent_object);		
    
    protected function getLayout()
    {
        $shim = $this->getShim();
        return $shim->getSingleton('core/layout');;	
    }
    
    protected function getCollector()
    {
        $shim = $this->getShim();
        return $collector = $shim->getSingleton('commercebug/collector')
        ->registerSingleCollector($this);				
    }

    public function getShim()
    {
        $shim = Zhangguoping_Commercebug_Model_Shim::getInstance();
        return $shim;	    
    }
    
    protected function getClassFile($className)
    {
        $r = new ReflectionClass($className);
        return str_replace("'",'',$r->getFileName());		
    }

    /**
    * If you attempt to call getStoreConfig before a store is
    * set, Magento throws an internal exception that it also 
    * catches, resulting in a 404.  This doesn't happen in a stock
    * install, but some users are reporting it in the wild.
    */
    protected function storeIsSet()
    {		
        if($this->_storeIsSet)
        {
            return true;
        }
        try
        { 
            $store = Mage::app()->getSafeStore();
            if(is_numeric($store->getId()))
            {
                $this->_storeIsSet = true;
                return true;
            }
            return false;
        }
        catch(Exception $e)
        {
            return false;
        }    					
        //just in case
        return false;
    }
    
    protected function _isOn($observer)
    {
        //if we're observing a store object, always return true.  Otherwise we may
        //get caught in an endless recursive loop
        if($observer->getEvent()->getObject() instanceof Mage_Core_Model_Store)
        {
            return true;
        }
        
        if($this->storeIsSet())
        {
            return $this->_isAccessClassAllowed();
        }
        else
        {
            return true; //error on the side of collecting the information
        }
    }
    
    protected function _isAccessClassAllowed()
    {
        return Mage::getSingleton('commercebug/ison')->isOn();
    }
    
}