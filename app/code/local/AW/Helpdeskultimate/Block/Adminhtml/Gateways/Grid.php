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


class AW_Helpdeskultimate_Block_Adminhtml_Gateways_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('gatewaysGrid');
        $this->setDefaultSort('title');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setNoFilterMassactionColumn(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('helpdeskultimate/gateway')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
            'column_css_class' => 'aw-hduat-gtw_id'
        ));
        $this->addColumn('is_active', array(
            'header' => $this->__('Active'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                0 => $this->__('No'),
                1 => $this->__('Yes')
            ),
            'column_css_class' => 'aw-hduat-gtw_is_active'
        ));
        $this->addColumn('title', array(
            'header' => $this->__('Title'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'title',
            'column_css_class' => 'aw-hduat-gtw_title'
        ));
        $this->addColumn('email', array(
            'header' => $this->__('Email'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'email',
            'column_css_class' => 'aw-hduat-gtw_email'
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
            ));
        return parent::_prepareColumns();
    }

    /**
     * Prepares mass action block
     * @return AW_Helpdeskultimate_Block_Adminhtml_Gateways_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('gateways');
        $this->getMassactionBlock()->setFormFieldName('gateways');
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->__('Are you sure?')
        ));
        return $this;
    }

    /**
     * Returns row url
     * @param object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/gateways/edit/', array('id' => $row->getId()));
    }
}
