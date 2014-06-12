<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Block_Product_Attribute_Boolean extends Bestmagento_BmProducts_Block_Product_Abstract
{
    protected function _beforeToHtml()
    {
        $collection = $this->getCollection();
        
        try {
            $collection->addAttributeToFilter("{$this->getAttributeCode()}", array('Yes' => true));
        } catch (Exception $e) {
            $collection = false;
        }
        
        $this->setProductCollection($collection);
        
        return parent::_beforeToHtml();
    }
    
}