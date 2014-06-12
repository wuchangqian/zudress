<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/

class Zhangguoping_Commercebug_Model_Jslabels extends Mage_Core_Model_Abstract
{	
    public function addTableLabelsToJson($json)
    {
        $json->labels = new stdClass();
        foreach($this->getLabels() as $name=>$label)
        {
            $json->labels->{$name} = $label;
        }			
        return $json;
    }
    
    public function getLabels()
    {
        return array(
        'observers'=>array(
            'column_1'=>$this->__('Event'),
            'column_2'=>$this->__('Configured Name'),
            'column_3'=>$this->__('Type'),
            'column_4'=>$this->__('Class'),
            'column_5'=>$this->__('Method'),
        ),	
        'collections'=>array(
            'collection_name'=>$this->__('Collection Name'),
            'times'=>$this->__('Times&nbsp;'),
            'note'=>$this->__('<strong>Note:</strong> This table captures collections which inherit from core Magento collection class (<code>Mage_Core_Model_Mysql4_Collection_Abstract</code>).  Legacy collections which inherit directly from <code>Varien_Data_Collection_Db</code> are not captured.')
        ),
        'events'=>array(
            'column_1'=>$this->__('Area'),
            'column_2'=>$this->__('Event Name'),
        ),			
        'models'=>array(
            'column_1'=>$this->__('Model Name'),
            'column_2'=>$this->__('Times&nbsp;'),
        ),
        'blocks'=>array(
            'column_1'=>$this->__('Block Name'),
            'column_2'=>$this->__('Times&nbsp;'),
            'column_3'=>$this->__('With Template'),
        ),
        'layouts'=>array(
            'column_1'				=>$this->__('Handles for this Request'),
            'view_page_layout'		=>$this->__('View Page Layout:'),
            'view_package_layout'	=>$this->__('View Package Layout:'),				
            'xml'					=>$this->__('XML'),				
            'text'					=>$this->__('Text'),	
            'view_graphviz'         =>$this->__('View Graphviz .dot Source'),
        ),	
        'controllers'=>array(
            'label'                 =>$this->__('Label'),
            'value'                 =>$this->__('Value'),
            'controller_class_name' =>$this->__('Controller Class Name'),
            'full_action_name'      =>$this->__('Full Action Name'),
            'module_name'           =>$this->__('Module Name'),
            'controller_name'       =>$this->__('Controller Name'),
            'action_name'           =>$this->__('Action Name'),
            'path_info'             =>$this->__('Path Info'),
            'cms_page_id'           =>$this->__('CMS Page ID'),            
        ),
        'other'=>array(
            'code_pool'             =>$this->__('Code Pool'),
            'type'                  =>$this->__('Type'),            
            'className'             =>$this->__('Class'),            
        ),
        'lookup'=>array(
            'instructions'  => $this->__('Enter an alias (<span class="classname">catalog/product</span>) or a class name '),
            'context'       => $this->__('Using alias context'),
            'all'           => $this->__('All'),        
            'model'         => $this->__('Model'),
            'block'         => $this->__('Block'),
            'helper'        => $this->__('Helper'),
            'resolves_to'   => $this->__('Resolves To'),            
        ),
        'system'=>array(
            'clear_cache'              => $this->__('Clear Cache'),
            'toggle_template'          => $this->__('Toggle Template Hints'),
            'toggle_block'             => $this->__('Toggle Add Block Hints'),            
            'toggle_magelogging'       => $this->__('Toggle Magento Logging'),                        
            'toggle_cblogging'         => $this->__('Toggle Commerce Bug Logging'),            
        ),
        );			
    }
    
    protected function __($label)
    {
        return $this->getShim()->helper('commercebug')->__($label);
    }
    
    function getShim()
    {
        $shim = Zhangguoping_Commercebug_Model_Shim::getInstance();
        return $shim;		
    }		
}