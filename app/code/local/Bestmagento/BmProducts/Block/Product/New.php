<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Block_Product_New extends Bestmagento_BmProducts_Block_Product_Attribute_Date
{
    protected $_title = 'New Products';
    protected $_priceSuffix = '-new';
    protected $_attributeCode = 'news_from_date,news_to_date';
    protected $_className = 'bmproducts-new';
}