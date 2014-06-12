<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Block_Review_New extends Bestmagento_BmProducts_Block_Product_Abstract
{
    protected $_title = 'Recent reviews';
    protected $_priceSuffix = '-review';
    protected $_className = 'bmproducts-review';
    
    protected function _beforeToHtml()
    {
        $collection = $this->getCollection('review/review_product_collection')
            ->addStatusFilter(1);
        
        $collection->getSelect()->order('rt.created_at DESC');
        
        $this->setProductCollection($collection);
        
        return parent::_beforeToHtml();
    }
    
    public function getReviewUrl($id)
    {
        return Mage::getUrl('review/product/view', array('id' => $id));
    }
}