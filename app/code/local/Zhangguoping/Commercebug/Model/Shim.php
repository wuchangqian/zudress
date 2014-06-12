<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Zhangguoping_Commercebug_Model_Shim
{
    static protected $_instance;
    
    public function createBlock($type, $name='', array $attributes = array())
    {
        $shim = self::getInstance();
        $layout = $shim->getSingleton('core/layout');

        $is_mage_2 = $this->_isMage2();
        if($is_mage_2)
        {
            $type = $this->_aliasToDefaultClass($type, 'Block');
            return $layout->createBlock($type, $name, $attributes);
        }
        else
        {
            return $layout->createBlock($type, $name, $attributes);
        }        
    }
    
    public function helper($class)
    {
        // var_dump($class);
        $is_mage_2 = $this->_isMage2();
        if($is_mage_2)
        {        
            $class = $this->_aliasToDefaultClass($class, 'Helper');            
            return Mage::helper($class);
        }
        else
        {
            #var_dump($class);
            return Mage::helper($class);
        }
    
    }
    
    public function getModel($modelClass = '', $arguments = array())
    {
        $is_mage_2 = $this->_isMage2();
        if($is_mage_2)
        {
            $class = $this->_aliasToDefaultClass($modelClass);
            return Mage::getModel($class, $arguments);
        }
        else
        {
            return Mage::getModel($modelClass, $arguments);
        }
    }
    
    public function getSingleton($modelClass = '', $arguments = array())
    {
        $is_mage_2 = $this->_isMage2();
        if($is_mage_2)
        {
            $class = $this->_aliasToDefaultClass($modelClass);
            return Mage::getSingleton($class, $arguments);
        }
        else
        {
            return Mage::getSingleton($modelClass, $arguments);
        }
    }
    
    protected function _getNamespaceForGroup($group)
    {
        switch($group)
        {
            case 'commercebug':
                return 'Alanstormdotcom';
            default:
                return 'Mage';
        }
        
    }
    
    protected function _aliasToDefaultClass($string,$context='Model')
    {
        if($context == 'Helper' && strpos($string, '/') === false)
        {
            $string .= '/data';
        }    
        
        list($group, $class) = explode('/',$string);    
        $namespace = $this->_getNamespaceForGroup($group);
        $class = $namespace . '_' . ucwords($group) . '_' . $context . '_' . ucwords($class);
        // var_dump($class);
        return $class;
    }
    
    protected function _isMage2()
    {
        $is_mage_1 = version_compare('1.99',Mage::getVersion(),'>');      
        return !$is_mage_1;
    }    

    public function isMage2()
    {
        return $this->_isMage2();
    }
    
    static public function getInstance()
    {
        if(!self::$_instance)
        {
            self::$_instance = new Zhangguoping_Commercebug_Model_Shim;
        }
        
        return self::$_instance;
    }
}