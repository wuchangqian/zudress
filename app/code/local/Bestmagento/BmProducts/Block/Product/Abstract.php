<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Block_Product_Abstract extends Mage_Catalog_Block_Product_Abstract
{
    const DEFAULT_PRODUCTS_COUNT = 6;
    const DEFAULT_COLUMNS_COUNT = 3;
	const DEFAULT_CATALOG_ID = 8;
    
    protected $_categoryFilter = array();
    protected $_priceFilter = array();
    protected $_addBundlePriceBlock = true;
    
    public function getCollection($collection = 'bmproducts/catalog_product_collection')
    {
        if (!$collection instanceof Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection) {
            $collection = Mage::getResourceModel($collection);
        }

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        
        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->setPageSize($this->getProductsCount())
            ->setCurPage(1);
        
        $this->applyDefaultPriceBlock();
        $this->applyPriceFilter($collection);
        $this->applyCategoryFilter($collection);
            
        return $collection;
    }

    public function getProductsCount()
    {
        if (!isset($this->_data['products_count'])) {
            $this->_data['products_count'] = self::DEFAULT_PRODUCTS_COUNT;
        }
        return $this->_data['products_count'];
    }

    public function getColumnsCount()
    {
        if (!isset($this->_data['columns_count'])) {
            $this->_data['columns_count'] = self::DEFAULT_COLUMNS_COUNT;
        }
        return $this->_data['columns_count'];
    }
    
    public function getTitle()
    {
        if (!isset($this->_data['title'])) {
            $this->_data['title'] = $this->_title;
        }
        return $this->_data['title'];
    }

    public function getCatId()
    {
        if (!isset($this->_data['catalog_id'])) {
            $this->_data['catalog_id'] = self::DEFAULT_CATALOG_ID;
        }
        return $this->_data['catalog_id'];
    }
    
    public function getClassName()
    {
        if (!isset($this->_data['class_name'])) {
            $this->_data['class_name'] = $this->_className;
        }
        return $this->_data['class_name'];
    }
    
    public function getAttributeCode()
    {
        if (!isset($this->_data['attribute_code'])) {
            $this->_data['attribute_code'] = $this->_attributeCode;
        }
        return $this->_data['attribute_code'];
    }
    
    public function getPriceSuffix()
    {
        if (!isset($this->_data['price_suffix'])) {
            $this->_data['price_suffix'] = $this->_priceSuffix;
        }
        return $this->_data['price_suffix'];
    }
    
    public function addPriceFilter($attribute = 'special_price', $condition = 'gt', $value = 0)
    {
        $this->_priceFilter[] = array(
            'attribute' => $attribute,
            'condition' => $condition,
            'value' => $value
        );
        return $this;
    }
    
    public function getPriceFilter()
    {
        return $this->_priceFilter;
    }
    
    public function addCategoryFilter($category)
    {
        $this->_categoryFilter[$category] = $category;
        return $this;
    }
    
    public function getCategoryFilter()
    {
        return $this->_categoryFilter;
    }
    
    public function setAddBundlePriceBlock($status)
    {
        $this->_addBundlePriceBlock = $status;
    }
    
    public function applyDefaultPriceBlock()
    {
        if ($this->_addBundlePriceBlock) {
            $this->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
        }
    }
    
    public function applyPriceFilter(&$collection)
    {
        foreach ($this->getPriceFilter() as $values) {
            $collection->addAttributeToFilter($values['attribute'], array($values['condition'] => $values['value']));
        }
    }
    
    public function applyCategoryFilter(&$collection)
    {
        if (count($this->getCategoryFilter())) {
            foreach ($this->getCategoryFilter() as $categoryId) {
                if ($categoryId != 'current') {
                    $collection->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId));
                } elseif ($category = Mage::registry('current_category')) {
                    $collection->addCategoryFilter($category);
                }
            }
        }
    }
}