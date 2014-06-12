<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento enterprise edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento enterprise edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdeskultimate
 * @version    2.10.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdeskultimate_Block_Adminhtml_Departments_Templates_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('departmentsTemplatesGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setNoFilterMassactionColumn(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('helpdeskultimate/template')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'filterable' => false,
            'index' => 'id',
            'filter_condition_callback' => 'aw_hdu_filter_by_ida_callback',
            'column_css_class' => 'aw-hduat-template_id'
        ));

        $this->addColumn('name', array(
            'header' => $this->__('Title'),
            'align' => 'left',
            'index' => 'name',
            'column_css_class' => 'aw-hduat-template_name'
        ));

        $this->addColumn('action',
            array(
                'header' => $this->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $this->__('Delete'),
                        'url' => array('base' => '*/*/delete'),
                        'confirm' => $this->__('Are you sure you want do this?'),
                        'field' => 'id'
                    ),
                    array(
                        'caption' => $this->__('Edit'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('templates');
        $this->getMassactionBlock()->setFormFieldName('templates');
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->__('Are you sure?')
        ));
        return $this;
    }

    protected function _prepareMassactionColumn()
    {
        $res = parent::_prepareMassactionColumn();
        $this->getColumn('massaction')->setWidth('40px');
        return $res;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/templates/edit/', array('id' => $row->getId()));
    }
}

function aw_hdu_filter_by_dep_callback($collection, $column)
{
    $title = $column->getFilter()->getValue();
    if (!trim($title)) return;
    $collection
        ->getSelect()
        ->where(
            "IF(d.name, d.name, '" . Mage::helper('helpdeskultimate')->getDepartment(0)->getName() . "') LIKE ?",
            "%$title%"
        );
}

function aw_hdu_filter_by_ida_callback($collection, $column)
{
    $title = $column->getFilter()->getValue();
    if (!trim($title)) return;
    $collection->getSelect()->where("main_table.id LIKE ?", "%$title%");
}
