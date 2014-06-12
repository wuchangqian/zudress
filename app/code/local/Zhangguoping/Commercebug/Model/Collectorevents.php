<?php 
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Zhangguoping_Commercebug_Model_Collectorevents extends Zhangguoping_Commercebug_Model_Observingcollector
{
    static protected $_events=array();
    static protected $_areas=array();
    static protected $_observers=array();
    public function collectInformation($observer)
    {
        //if modules config hasn't loaded yet Bad Things happen
        if(!Mage::getConfig()->getNode('modules'))
        {
            return;
        }
                    
        $collector = $this->getCollector(); //seems to do nothing, but actually registers collector
        self::$_events[]    = $observer->event;
        self::$_areas[]     = $observer->area;
    }

    public function collectObservers($name, $configuration)
    {
        if(!$configuration || !is_array($configuration) || !array_key_exists('observers', $configuration) )
        {
            return;
        }
        $observers = $configuration['observers'];
        foreach($observers as $configued_name=>$information)
        {
            if(array_key_exists('model',$information) && strpos($information['model'], 'Commercebug') !== false)
            {
                continue;
            }
            self::$_observers[$name][$configued_name] = $information;
        }
    }
    
    public function addToObjectForJsonRender($json)
    {
        $json->events						= array();
        foreach(self::$_events as $event)
        {
            $json->events[] = $event;				
        }					
        
        $json->event_areas						= array();
        foreach(self::$_areas as $area)
        {
            $json->event_areas[] = $area;				
        }	
        
        $json->observers                        = array();
        foreach(self::$_observers as $key=>$observer)
        {
            $observer['commercebug_name']   = $key;
            $json->observers[] = $observer;
        }
        
        return $json;
    }
    
    public function createKeyName()
    {
        return 'events';
    }
}
