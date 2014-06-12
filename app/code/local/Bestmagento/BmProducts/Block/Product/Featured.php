<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Block_Product_Featured extends Bestmagento_BmProducts_Block_Product_Abstract
{
    protected $_title = 'Featured Products';
    protected $_priceSuffix = '-featured';
    protected $_className = 'bmproducts-featured';

	protected function _beforeToHtml()
    {
        $collection = $this->getCollection()
            ->addCategoryFilter(Mage::getModel('catalog/category')->load($this->getCategoryId()));
        
        $this->setProductCollection($collection);

        return parent::_beforeToHtml();
    }
}