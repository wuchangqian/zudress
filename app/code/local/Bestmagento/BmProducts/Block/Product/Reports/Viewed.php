<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Block_Product_Reports_Viewed extends Mage_Reports_Block_Product_Viewed
{
    protected function _hasViewedProductsBefore()
    {
        return true;
    }
}
