<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Block_Product_Special extends Bestmagento_BmProducts_Block_Product_Attribute_Date
{
    protected $_title = 'Special products';
    protected $_priceSuffix = '-special';
    protected $_attributeCode = 'special_from_date,special_to_date';
    protected $_className = 'bmproducts-special';
    
    protected function _beforeToHtml()
    {
        $this->addPriceFilter('special_price');
        return parent::_beforeToHtml();
    }
}