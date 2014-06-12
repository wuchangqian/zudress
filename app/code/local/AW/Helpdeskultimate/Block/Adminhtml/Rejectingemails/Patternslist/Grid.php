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


class AW_Helpdeskultimate_Block_Adminhtml_Rejectingemails_Patternslist_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('patternsListGrid')
            ->setSaveParametersInSession(true);
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('helpdeskultimate/rpattern')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => $this->__('ID'),
            'width' => '100px',
            'index' => 'id',
            'column_css_class' => 'aw-hduat-rp_id'
        ));

        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'index' => 'name',
            'column_css_class' => 'aw-hduat-rp_name'
        ));

        $this->addColumn('is_active', array(
            'header' => $this->__('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => Mage::getModel('helpdeskultimate/source_status')->toShortOptionArray(),
            'width' => '100px',
            'column_css_class' => 'aw-hduat-rp_is_active'
        ));

        $this->addColumn('scope', array(
            'header' => $this->__('Scope'),
            'index' => 'scope',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'AW_Helpdeskultimate_Block_Widget_Grid_Column_Renderer_Scope',
            'column_css_class' => 'aw-hduat-rp_scope'
        ));

        $this->addColumn('pattern', array(
            'header' => $this->__('Pattern'),
            'index' => 'pattern',
            'column_css_class' => 'aw-hduat-rp_pattern'
        ));

        $this->addColumn('actions', array(
            'header' => $this->__('Actions'),
            'width' => '150px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $this->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                ),
                array(
                    'caption' => $this->__('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                    'confirm' => $this->__('Are you sure you want do this?')
                )
            ),
            'filter' => false,
            'sortable' => false,
            'is_system' => true
        ));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('patterns');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/rejectingemails/patternMassDelete', array()),
            'confirm' => $this->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('status', array(
            'label' => $this->__('Change status'),
            'url' => $this->getUrl('*/rejectingemails/patternMassStatus'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->__('Status'),
                    'values' => Mage::getModel('helpdeskultimate/source_status')->toOptionArray()
                )
            )
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
