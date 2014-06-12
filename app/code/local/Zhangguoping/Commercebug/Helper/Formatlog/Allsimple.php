<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/

class Zhangguoping_Commercebug_Helper_Formatlog_Allsimple extends Zhangguoping_Commercebug_Helper_Data
{
    protected $_info=array();
    public function format($thing)
    {
        $object = Mage::getModel('commercebug/jsonbroker')->jsonDecode($thing);						
        $this->formatControllerRequest($object);
        $this->formatModels($object);
        $this->formatCollections($object);
        $this->formatBlocks($object);
        $this->formatLayout($object);
        $this->formatEvents($object);
        $this->formatObservers($object);
        $results = $this->_formatInfo();
        return $results;		
    }
    
    public function formatEvents($object)
    {
        $result = "\nEvents \n--------------------------------------------------" . "\n";		
        if(isset($object->events))
        {
            $result .= implode("\n", $object->events);
        }
        $this->_info[] = $result;	
        return $this;			            
    }

    public function formatObservers($object)
    {
        $result = "\nObservers \n--------------------------------------------------" . "\n";		

        if(isset($object->observers))
        {
            foreach($object->observers as $item)
            {
                foreach(get_object_vars($item) as $name=>$props)
                {
                    if($name == 'commercebug_name')
                    {
                        continue;
                    }
                    
                    if(!is_object($props))
                    {
                        continue;
                    }
                    $info = array();
                    $info[] = $name;
                    unset($props->args);
                    $info = array_merge($info, (array)$props);
                    $result .= implode("\t", $info) . "\n";
                }                
                
            }
        }
        $this->_info[] = $result;	
        return $this;			            
    }    
    
    
    public function formatLayout($object)
    {
        $result = "\nLayout Handles \n--------------------------------------------------" . "\n";
        foreach($object->layout->handles as $handle)
        {
            $result .= '<'.$handle.' />' . "\n";
        }
        $this->_info[] = $result;	
        return $this;			
    }
    
    public function formatBlocks($object)
    {
        $result = "\nBlocks \n--------------------------------------------------" . "\n";
        foreach($object->blocks as $pair=>$times)
        {
            list($class, $template) = explode('::',$pair);
            $result .= $class . "\t" . "instantiated" . "\t" . $times . "\t" . "times" . "\n";
            $result .= 'With Template: ' . $template . "\n";
            $result .= $object->blockFiles->{$pair} . "\n\n";
        }
        $this->_info[] = $result;	
        return $this;		
    }
    
    public function formatCollections($object)
    {
        $result = "\nCollections \n--------------------------------------------------" . "\n";
        foreach($object->collections as $class=>$times)
        {
            $result .= $class . "\t" . "instantiated" . "\t" . $times . "\t" . "times" . "\n";
            $result .= 'Collects Mage::getModel("'.$object->collectionModels->{$class}.'");' . "\n";
            $result .= $object->collectionFiles->{$class} . "\n\n";
        }
        $this->_info[] = $result;	
        return $this;
    }
    
    public function formatModels($object)
    {
        $result = "\nModels \n--------------------------------------------------" . "\n";
        $object->models = !isset($object->models) ? array() : $object->models;
        foreach($object->models as $class=>$times)
        {
            $result .= $class . "\t" . "instantiated" . "\t" . $times . "\t" . "times" . "\n";
            $result .= $object->modelFiles->{$class} . "\n\n";
        }
        $this->_info[] = $result;
        return $this;
    }
    
    protected function _prepObject($object)
    {
        $o = new stdClass();
        $object->controller = !isset($object->controller) ? $o : $object->controller;
        $object->controller->className = !isset($object->controller->className) ? '' : $object->controller->className;
        $object->controller->fullActionName = !isset($object->controller->fullActionName) ? '' : $object->controller->fullActionName;
        $object->controller->fileName = !isset($object->controller->fileName) ? '' : $object->controller->fileName;
        
        $object->request = !isset($object->request) ? $o : $object->request;
        $object->request->moduleName = !isset($object->request->moduleName) ? '' : $object->request->moduleName;
        $object->request->controllerName = !isset($object->request->controllerName) ? '' : $object->request->controllerName;
        $object->request->actionName = !isset($object->request->actionName) ? '' : $object->request->actionName;
        $object->request->pathInfo = !isset($object->request->pathInfo) ? '' : $object->request->pathInfo;
        return $object;
    }
    public function formatControllerRequest($object)
    {			
        $object = $this->_prepObject($object);
        $result = "\n\nController/Request \n--------------------------------------------------" . "\n"
        . 'Controller Class Name:' 	. "\t" . $object->controller->className . "\n" 
        . 'Controller Class Name:' 	. "\t" . $object->controller->fileName . "\n" 
        . 'Full Action Name:' 		. "\t" . $object->controller->fullActionName . "\n"
        . 'Module Name:' 			. "\t" . $object->request->moduleName . "\n"
        . 'Controller Name:' 		. "\t" . $object->request->controllerName . "\n"
        . 'Action Name:' 			. "\t" . $object->request->actionName . "\n"
        . 'Path Info:' 				. "\t" . $object->request->pathInfo . "\n";
        
        $this->_info[] = $result;		
        return $this;
    }
    public function getOutputFor($key, $value)
    {
        return call_user_func_array(array($this,'format'.ucwords($key)),array($value));			
    }

    public function _formatInfo()
    {
        $results  = "\n" . '+--START------------------------------------------+' . "\n";
        foreach($this->_info as $item)
        {
            $results .= $item . "\n";
        }
        $results .= '+--END--------------------------------------------+' . "\n";			
        return $results;
    }
    
    public function __call($func,$args)
    {
        $this->_info[] = ("Don't know what to do with a " . $func . "\n");
    }
}