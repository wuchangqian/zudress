<?php
/**
* Copyright © Pulsestorm LLC: All rights reserved
*/
class Zhangguoping_Commercebug_Model_Observer_Dot
{		
    const RENDER_FULL_LABELS		= true;
    static protected $_stack   		= array();
    static protected $_graphs 		= array();
    static protected $_definitions	= array();
    
    public function before($observer)
    {
        $name = $observer->getBlock()->getNameInLayout();
        $name = $name ? $name : 'unknown';		
        self::$_stack[] = $name;
    }
    
    public function after($observer)
    {			
// 			try
// 			{
            $this->_watch($observer);
// 			}
// 			catch(Exception $e)
// 			{
// 				Mage::Log("Failed to _watch " . __CLASS__);				
// 			}
        array_pop(self::$_stack);
        self::renderGraph();
    }
    
    static public function shouldRenderFullLabels()
    {
        return self::RENDER_FULL_LABELS;
    }
    static public function renderGraph()
    {
        //Mage::Log('--------------------------------------------------');
        $graph = 'digraph g {

ranksep=6
node [
    fontsize = "16"
    shape = "rectangle"
    width =3
    height =.5
];
edge [
];         ';
        $graph .= implode(";\n",self::$_graphs) . ';';
        
        if(self::shouldRenderFullLabels())
        {
            $graph .= implode(";\n",self::$_definitions) . ';';
        }
        $graph .= '}';
        return $graph;
        //Mage::Log($graph);
        //Mage::Log('--------------------------------------------------');
    
    }
    
    protected function _getAssumedParentNameFromStack()
    {
        $index = count(self::$_stack)-2;
        return array_key_exists($index, self::$_stack) ? self::$_stack[$index] : 'Unknown Parent/Direct Instantiation';
    }
    
    /**
    * Happens with getChildChild html ... I think
    */		
    protected function _isAssumeParentAndRealParentMismatch($parent)
    {
        return (is_object($parent) && $parent->getNameInLayout() != $this->_getAssumedParentNameFromStack());
    }
    
    protected function _checkAssumptions($parent, $block)
    {
        if(is_object($parent) && $this->_isAssumeParentAndRealParentMismatch($parent))
        {
// 					Mage::Log('Assumed Parent Mismatch');
// 					Mage::Log('Real Parent: ' . $parent->getNameInLayout());
// 					Mage::Log('Assumed Parent:' . $this->_getAssumedParentNameFromStack());
// 					Mage::Log('Block: ' . $block->getNameInLayout());
// 					Mage::Log(self::$_stack);
// 					Mage::Log('END: Assumed Parent Mismatch');
        }
    }
    
    protected function _isUnwantedBlock($block)
    {
        //skip Commercebug Blocks
        return strpos(get_class($block), 'Commercebug') !== false;
    }
    
    protected function _isParentlessAnonymous($parent, $block)
    {
        return !is_object($parent) && strpos($block->getNameInLayout(),'ANONYMOUS_') === 0;
    }
    
    protected function _isGoofyFacade($parent, $block)
    {
        return is_object($parent) && ($parent instanceof Mage_Core_Block_Template_Facade);
    }
    
    protected function _watch($observer)
    {			
        $block 	= $observer->getBlock();

        if($this->_isUnwantedBlock($block)) 
        { 
            return; 
        } 
        
        $style = ' [style=solid]';			
        $parent 		= $block->getParentBlock();			
        $name_block  	= $block->getNameInLayout();
        $name_parent  	= 'unknown';
        
        $this->_checkAssumptions($parent, $block);
        
        if($this->_isParentlessAnonymous($parent, $block) )
        {						
            $style = ' [style=dashed]';
            $parent = new Varien_Object();
            $parent->setNameInLayout($this->_getAssumedParentNameFromStack() );
            $name_parent = $parent->getNameInLayout();
        }
        else if ($this->_isAssumeParentAndRealParentMismatch($parent, $block))
        {
            $style = ' [style=dotted]';
            $parent = new Varien_Object();
            $parent->setNameInLayout($this->_getAssumedParentNameFromStack());
            $name_parent = $parent->getNameInLayout();							
        }
        else if ($this->_isGoofyFacade($parent, $block))
        {
            $style = ' [style=dotted]';
            $parent = new Varien_Object();
            $parent->setNameInLayout($this->_getAssumedParentNameFromStack());
            $name_parent = $parent->getNameInLayout();				
        }
        else if (is_object($parent))
        {
            $name_parent = $parent->getNameInLayout();
        }
        
        if(!$parent)
        {
            self::$_graphs[] = '"' . $block->getNameInLayout() . '"' . $style;					
        }
        else
        {			
            self::$_graphs[] = '"' . $parent->getNameInLayout() . '"' . '->' . '"' . $block->getNameInLayout() . '"' . $style;					
        }	
        
        $template = $block->getTemplate() ? $block->getTemplate() : 'NO TEMPLATE';
        $definition = '"' . $name_block . '"' . '[label="' . 
        $name_block .  '\\\\n' . 
        get_class($block) . '\\\\n' . 
        $template . 
        '"]';
        
        self::$_definitions[$name_block] = $definition;
    }
    
    protected function _checkInside()
    {
    }
}