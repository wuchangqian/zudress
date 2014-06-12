<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/

class Zhangguoping_Commercebug_Block_Html extends Mage_Core_Block_Template
{	
    public function __construct()
    {
        //can't do this, the scriptpath is set in the actual render view method
        //$this->setScriptPath(dirname(__FILE__) . '/../static');
    }

    protected function _checkValidScriptPath($scriptPath)
    {
        $paths_to_check = array(Mage::getBaseDir('design'),Mage::getModuleDir('', 'Zhangguoping_Commercebug'));
        $valid			= false;
        foreach($paths_to_check as $path)
        {
            if(strpos($scriptPath, realpath($path)) === 0)
            {
                $valid = true;
            }
        }
        return $valid;		
    }
    
    public function setScriptPath($dir)
    {
        $scriptPath = realpath($dir);
        if ($this->_checkValidScriptPath($scriptPath)) {
            $this->_viewDir = $dir;
        } else {
            Mage::log('Not valid script path:' . $dir, Zend_Log::CRIT, null, null, true);
        }
        return $this;
    }

    public function fetchView($fileName)			    
    {	    	
        $module_dir = Mage::getModuleDir('', 'Zhangguoping_Commercebug');
        $this->setScriptPath($module_dir . '/static');
        return parent::fetchView($this->getTemplate());
    }
    
    const PATH_FRONTEND_COMMERCEBUG = 'commercebug';
    public function getPathSkin()
    {        
        if(!$this->getShim()->isMage2())
        {	    	    
            $url = Mage::getBaseUrl('js') . 'commercebug';
        }
        else
        {
            $base_skin = 'http://' . $_SERVER['HTTP_HOST'] . '/pub/js/';
        }        
        return $url;
    }
 
    const UPDATE_URL = 'http://commercebug.alanstorm.com/index.php/version?callback=?';
    public function getUpdateUrl()
    {
        return self::UPDATE_URL;
    }	 		 	


    //86400 = 1 day	 	
    const UPDATE_CHECK_RATE_IN_SECONDS = 86400;
    public function getCheckForUpdatesFlag()
    {			
        return false;
        $shim = $this->getShim();
        $last_time = $shim->getSingleton('commercebug/jsonbroker')->jsonDecode(Mage::getStoreConfig('commercebug/options/update_last_check'));
        $last_time = $last_time->date;	 			
        if((strToTime($last_time) + self::UPDATE_CHECK_RATE_IN_SECONDS) > time())
        {
            return false;
        }	 		
        
        //if we're still here, check teh config flag
        return Mage::getStoreConfig('commercebug/options/check_for_updates');
    }

    public function fetchUseKeyboardShortcuts()
    {
        return Mage::getStoreConfig('commercebug/options/keyboard_shortcuts');		
    }
    public function calculateNextUpdateCheck()
    {
        $last_time = $this->getShim()->getModel('commercebug/jsonbroker')->jsonDecode(Mage::getStoreConfig('commercebug/options/update_last_check'));		
        return date(DATE_RFC822, strToTime($last_time->date)+self::UPDATE_CHECK_RATE_IN_SECONDS);
    }
    public function getLastUpdateCheck()
    {
        $last_time = $this->getShim()->getModel('commercebug/jsonbroker')->jsonDecode(Mage::getStoreConfig('commercebug/options/update_last_check'));		
        return date(DATE_RFC822, strToTime($last_time->date));		
    }
    public function resetLastUpdated()
    {
        $object				= new StdClass();
        $object->date		= date(DATE_RFC822);	 		
        
        $groups_value 		= array();			
        $groups_value['options']['fields']['update_last_check']['value'] = $this->getShim()->getModel('commercebug/jsonbroker')->jsonEncode($object);
        $model = $this->getShim()->getModel('adminhtml/config_data')
            ->setSection('commercebug')
            ->setWebsite(null)
            ->setStore(null)
            ->setGroups($groups_value)
            ->save();
            
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();				
    }
    
    public function fetchResultsFromLastUpdateCheck()
    {
        return Mage::getStoreConfig('commercebug/options/update_last_check');	 	
    }
    
    public function includeStatic($path)
    {	 		
        ob_start();
        include('app/code/local/Alanstormdotcom/Commercebug/static/' . $path);
        return ob_get_clean();
    }
    
    public function getLayout()
    {
        $shim = $this->getShim();
        return $shim->getSingleton('core/layout');
    }
    
    function getShim()
    {
        $shim = Zhangguoping_Commercebug_Model_Shim::getInstance();
        return $shim;		
    }        
    
    public function getTabIdPairs()
    {
        $tab_id_pairs = array();
        if(!$this->getConfig())
        {
            return $tab_id_pairs;
        }
        foreach($this->getConfig()->xpath('*') as $node)
        {
            $tab_id_pairs[$node->getName()] = (string) $node->title;
        }		
        return $tab_id_pairs;
    }
    
    public function getTabIdAndHtmlPairs()
    {			
        $tab_html_pairs = array();
        if(!$this->getConfig())
        {
            return $tab_html_pairs;
        }
        foreach($this->getConfig()->xpath('*') as $node)
        {
            $tab_html_pairs[$node->getName()] = $this->getShim()->createBlock(
            (string) $node->block
            )
            ->setConfigNode($node)
            ->toHtml();
        }		
        return $tab_html_pairs;
    }

    protected function getConfig()
    {
        $config = Mage::getConfig()->loadModulesConfiguration('commercebug.xml');
        if(!$this->_config)
        {
            $this->_config = Mage::getConfig()->loadModulesConfiguration('commercebug.xml')->getNode('tabs');
        }
        
        return $this->_config;
    }
    
    protected function _renderJsonAssignment($var_name, $object)
    {
        if(!is_object($object))
        {
            throw new Exception("Attempted to add non object as json");
        }
        
        $var_name = strToLower(preg_replace('%[^a-z_]%i','',$var_name));
        return 'var ' . $var_name . ' = ' . $this->getShim()->getSingleton('commercebug/jsonbroker')->jsonEncode($object);
    }    
}