<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/

class Zhangguoping_Commercebug_Model_Collector extends Mage_Core_Helper_Abstract
{
    protected $controller;
    protected $layout;
    protected $request;
    
    protected $models = array();
    protected $blocks = array();

    protected $_singleCollectors=array();		
    public function registerSingleCollector(Zhangguoping_Commercebug_Model_Observingcollector $object)
    {
        if(!in_array($object, $this->_singleCollectors))
        {
            $this->_singleCollectors[] = $object;
        }
        return $this;
    }		
            
    //renders as json.
    public function asJson()
    {
        $shim = $this->getShim();
        $json = new stdClass();
        
        foreach($this->_singleCollectors as $single_collector)
        {
            $json = $single_collector->addToObjectForJsonRender($json);	
        }

        $json = $shim->getSingleton('commercebug/jslabels')->addTableLabelsToJson($json);
        
        //add standard objects to surpress from other models tab
        $json->standardModels = preg_split('%\s%',Mage::getStoreConfig('commercebug/options/standard_classes'));
        
//         array(
//         'Mage_Core_Model_App','Mage_Core_Model_Config','Mage_Core_Model_Config_Base',
//         'Mage_Core_Model_Config_Options','Mage_Core_Model_Locale','Mage_Core_Model_Config_Element',
//         'Mage_Core_Model_Cache','Mage_Core_Model_App_Area','Mage_Core_Model_Website',
//         'Mage_Core_Model_Store_Group','Mage_Core_Model_Store','Mage_Core_Model_Store_Exception',
//         'Mage_Core_Model_Translate_Expr', 'Mage_Core_Model_Translate', 'Mage_Core_Model_Cookie',
//         'Mage_Core_Model_Url', 'Mage_Core_Model_Layout', 'Mage_Core_Model_Layout_Element',
//         'Mage_Core_Model_Layout_Update', 'Mage_Core_Model_Session', 'Mage_Core_Model_Message_Collection',
//         'Mage_Core_Model_Design_Package', 'Mage_Core_Model_Design', 'Mage_Core_Model_Translate_Inline',
//         'Mage_Log_Model_Visitor', 'Mage_PageCache_Model_Observer', 'Mage_Persistent_Model_Observer',
//         'Mage_Persistent_Model_Session', 'Mage_Persistent_Model_Observer_Session');
        

        $json = $shim->getSingleton('commercebug/jsonbroker')->jsonEncode($json); 
        
        #$message = __CLASS__ . 'Serialized:' . $json;
        $message = $shim->helper('commercebug/log')->format($json);
        $shim->helper('commercebug/log')->log($message);						
        return $json;			
    }
    
    private function getClassFile($className)
    {
        $r = new ReflectionClass($className);
        return $r->getFileName();		
    }
    
    function getShim()
    {
        $shim = Zhangguoping_Commercebug_Model_Shim::getInstance();
        return $shim;		
    }		
}