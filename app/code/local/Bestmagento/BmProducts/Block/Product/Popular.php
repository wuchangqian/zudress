<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Block_Product_Popular extends Bestmagento_BmProducts_Block_Product_Abstract
{
    protected $_title = 'Popular Products';
    protected $_priceSuffix = '-popular';
    protected $_className = 'bmproducts-popular';
    
    protected function _beforeToHtml()
    {
        $collection = $this->getCollection('bmproducts/reports_product_collection')
            ->addViewsCount();
        
        $this->setProductCollection($collection);

        return parent::_beforeToHtml();
    }
}