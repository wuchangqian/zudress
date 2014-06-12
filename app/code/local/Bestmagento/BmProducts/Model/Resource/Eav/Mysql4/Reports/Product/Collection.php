<?php
/**
 * This is the part of 'BmProducts' module for Magento,
 * which allows easy access to product collection
 * with flexible filters
 */

class Bestmagento_BmProducts_Model_Resource_Eav_Mysql4_Reports_Product_Collection 
    extends Mage_Reports_Model_Mysql4_Product_Collection
{
    public function isEnabledFlat()
    {
        return false;
    }
    
    public function addOrderedQty($from = '', $to = '')
    {
        $qtyOrderedTableName = $this->getTable('sales/order_item');
        $qtyOrderedFieldName = 'qty_ordered';

        $productIdFieldName = 'product_id';

        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        //$productTypes = $this->getConnection()->quoteInto(' AND (e.type_id NOT IN (?))', $compositeTypeIds);

        if ($from != '' && $to != '') {
            $dateFilter = " AND `order`.created_at BETWEEN '{$from}' AND '{$to}'";
        } else {
            $dateFilter = "";
        }

        $this->getSelect()/*->reset()->from(*/->joinInner(
            array('order_items' => $qtyOrderedTableName),
            'cat_index.product_id=order_items.product_id',
            array('ordered_qty' => "SUM(order_items.{$qtyOrderedFieldName})")
        );

        $_joinCondition = $this->getConnection()->quoteInto(
            'order.entity_id = order_items.order_id AND order.state<>?', Mage_Sales_Model_Order::STATE_CANCELED
        );
        $_joinCondition .= $dateFilter;
        $this->getSelect()->joinInner(
            array('order' => $this->getTable('sales/order')),
            $_joinCondition,
            array()
        );


        $this->getSelect()
            ->joinInner(array('pet' => $this->getProductEntityTableName()),
                "pet.entity_id = order_items.{$productIdFieldName} AND e.entity_type_id = {$this->getProductEntityTypeId()}")//{$productTypes}
            ->group('pet.entity_id')
            ->having('ordered_qty > 0');

        return $this;
    }
    
    public function addViewsCount($from = '', $to = '')
    {
        /**
         * Getting event type id for catalog_product_view event
         */
        foreach (Mage::getModel('reports/event_type')->getCollection() as $eventType) {
            if ($eventType->getEventName() == 'catalog_product_view') {
                $productViewEvent = $eventType->getId();
                break;
            }
        }

        $this->getSelect()/*->reset()*/
            /*->from(
                array('_table_views' => $this->getTable('reports/event')),
                array('views' => 'COUNT(_table_views.event_id)'))*/
            ->joinInner(
                array('_table_views' => $this->getTable('reports/event')),
                'cat_index.product_id=_table_views.object_id',
                array('views' => 'COUNT(_table_views.event_id)'))
            ->join(array('pet' => $this->getProductEntityTableName()),
                "pet.entity_id = _table_views.object_id AND pet.entity_type_id = {$this->getProductEntityTypeId()}")
            ->where('_table_views.event_type_id = ?', $productViewEvent)
            ->group('pet.entity_id')
            ->order('views desc')
            ->having('views > 0');

        if ($from != '' && $to != '') {
            $this->getSelect()
                ->where('logged_at >= ?', $from)
                ->where('logged_at <= ?', $to);
        }

        return $this;
    }
}