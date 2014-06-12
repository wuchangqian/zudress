<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Block_Product_Random extends Bestmagento_BmProducts_Block_Product_Abstract
{
    protected $_title = 'Random Products';
    protected $_priceSuffix = '-random';
    protected $_className = 'bmproducts-random';

    protected function _beforeToHtml()
    {
        $collection = $this->getCollection()
            ->addCategoryFilter(Mage::getModel('catalog/category')->load($this->getCategoryId()));
        
        $this->setProductCollection($collection);

        return parent::_beforeToHtml();
    }

}