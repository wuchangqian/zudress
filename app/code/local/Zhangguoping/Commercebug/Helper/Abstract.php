<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
abstract class Zhangguoping_Commercebug_Helper_Abstract extends Mage_Core_Helper_Data
{
    public function isModuleOutputEnabled($moduleName = null)
    {        	
        $shim = $this->getShim();
        if(is_callable(array($shim->helper('core'),'isModuleOutputEnabled')))
        {
            return parent::isModuleOutputEnabled();
        }
                     
        if ($moduleName === null) {
            $moduleName = $this->_getModuleName();
        }
        if (Mage::getStoreConfigFlag('advanced/modules_disable_output/' . $moduleName)) {
            return false;
        }
        return true;
    }        	
    
    public function getShim()
    {
        $shim = Zhangguoping_Commercebug_Model_Shim::getInstance();
        return $shim;	    
    }
    
}