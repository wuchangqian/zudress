<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Block_Product_Bestseller extends Bestmagento_BmProducts_Block_Product_Abstract
{
    protected $_title = 'Bestsellers';
    protected $_priceSuffix = '-bestseller';
    protected $_className = 'bmproducts-bestseller';
    
    protected function _beforeToHtml()
    {
        $collection = $this->getCollection('bmproducts/reports_product_collection')
            ->addOrderedQty()
            ->addAttributeToSelect('ordered_qty')
            ->setOrder('ordered_qty', 'desc');
        
        $this->setProductCollection($collection);

        return parent::_beforeToHtml();
    } 
}