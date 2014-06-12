<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/

class Zhangguoping_Commercebug_Helper_Classurilookup extends Zhangguoping_Commercebug_Helper_Abstract
{
    public function classToUri($class)
    {
        $parts = $this->splitClassIntoParts($class);
        if(count($parts) == 4)
        {
            list($namespace, $module, $type, $name) = $parts;
            $uri = strToLower($module) .
            '/' .
            strToLower($name);		
        }
        else
        {
            $uri = '';
        }
        return $uri;
    }	
    
    public function classToType($class)
    {
        
        $parts 									= $this->splitClassIntoParts($class);
        if(count($parts) == 4)
        {
            list($namespace, $module, $type, $name) = $parts;			
            return strToLower($type);
        }
        else
        {
            return '';
        }
    }
    
    protected function splitClassIntoParts($class)
    {
        return preg_split('{_}',$class,4);
    }
}