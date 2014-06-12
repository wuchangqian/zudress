<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Zhangguoping_Commercebug_Model_Events
{
    public function capture()
    {
        $app = Mage::app();
        
        $r = new ReflectionObject($app);
        $rp = $r->getProperty('_events');
        if(method_exists($rp, 'setAccessible'))
        {
            $rp->setAccessible(true);
            $events = $rp->getValue($app);        
        }
        else
        {
            $events = $this->_phpFivePointTwoReflection($app);
        }
        
        $collector  = new Zhangguoping_Commercebug_Model_Collectorevents;        
        foreach($events as $area=>$event)
        {
            foreach($event as $event_name=>$configuration)
            {
                $o        = new stdClass();
                $o->area  = $area;
                $o->event = $event_name;
                $o->data  = array();
                $collector->collectInformation($o);
                
                $collector->collectObservers($event_name, $configuration);
                
            }        
        }
    }
        
    protected function _phpFivePointTwoReflection($app)
    {
        $serialized = serialize($app);
        
        //grab the events portion
        $parts = preg_split('%.\*._events";%',$serialized);
        
        //remove pre first bracket and stow way for later
        $ser_events = $parts[1];
        $ser_events = explode('{', $ser_events);
        $start      = array_shift($ser_events);
        $ser_events = implode('{', $ser_events);
        
        //parse through until brackets balance — makes assumptions about 
        //_Events not containing a { or a }
        $length         = strlen($ser_events);
        $bracket_count  = 0;
        for($i=0;$i<$length;$i++)
        {            
            $chr = $ser_events[$i];
            
            if($chr == '{')
            {
                $bracket_count++;
            }

            if($chr == '}')
            {
                $bracket_count--;
            }
            
            if($bracket_count == -1)
            {
                break;
            }
        }
        //put the string back together
        $ser_events = substr($ser_events, 0, $i);
        $ser_events = $start . '{' . $ser_events . '}';
        $events = $this->_unserializeEvents($ser_events);
        $events = is_array($events) ? $events : array();
        return $events;        
    }

    /**
    * Wrapping in method in case the @ causes a problem (rewrite)
    */    
    protected function _unserializeEvents($ser_events)
    {
        return @unserialize($ser_events);
    }
}