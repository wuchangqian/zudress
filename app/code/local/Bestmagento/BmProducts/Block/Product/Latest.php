<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Block_Product_Latest extends Bestmagento_BmProducts_Block_Product_Abstract
{
    protected $_title = 'Latest';
    protected $_priceSuffix = '-latest';
    protected $_className = 'bmproducts-latest';
    
    protected function _beforeToHtml()
    {
        $collection = $this->getCollection('bmproducts/reports_product_collection')

            ->addAttributeToSelect('updated_at')
            ->setOrder('updated_at', 'desc');
        
        $this->setProductCollection($collection);

        return parent::_beforeToHtml();
    } 
}