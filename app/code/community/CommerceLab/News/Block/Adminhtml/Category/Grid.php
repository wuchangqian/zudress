<?php
/**
 * CommerceLab Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the CommerceLab License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://commerce-lab.com/LICENSE.txt
 *
 * @category   CommerceLab
 * @package    CommerceLab_News
 * @copyright  Copyright (c) 2011 CommerceLab Co. (http://commerce-lab.com)
 * @license    http://commerce-lab.com/LICENSE.txt
 */

class CommerceLab_News_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('categoryGrid');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('clnews/category')->getCollection();
        if (!Mage::app()->isSingleStoreMode()) {
            $tableName = Mage::getSingleton('core/resource')->getTableName('clnews_category_store');
            $collection->getSelect()->joinLeft($tableName, 'main_table.category_id = '. $tableName . '.category_id', array($tableName . '.store_id as store_id'));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('category_id', array(
            'header'    => Mage::helper('clnews')->__('ID'),
            'align'     =>'right',
            'width'     => '50',
            'index'     => 'category_id',
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('clnews')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
        ));

        $this->addColumn('url_key', array(
            'header'    => Mage::helper('clnews')->__('URL Key'),
            'align'     =>'left',
            'index'     => 'url_key',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('sort_order', array(
            'header'    => Mage::helper('clnews')->__('Sort Order'),
            'align'     => 'left',
            'width'     => '50',
            'index'     => 'sort_order',
      ));

      $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('clnews')->__('Action'),
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('clnews')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'width'     => '70',
                'index'     => 'stores',
                'is_system' => true,
                'filter'    => false,
                'sortable'  => false,
                ));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('category_id');
        $this->getMassactionBlock()->setFormFieldName('category');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('clnews')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('clnews')->__('Are you sure?')
        ));

        return $this;
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
