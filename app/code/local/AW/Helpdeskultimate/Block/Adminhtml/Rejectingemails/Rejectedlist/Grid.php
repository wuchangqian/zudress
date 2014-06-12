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

class AW_Helpdeskultimate_Block_Adminhtml_Rejectingemails_Rejectedlist_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('rejectedEmailsGrid')
                ->setSaveParametersInSession(true)
                ->setDefaultSort('id', 'desc');
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('helpdeskultimate/popmessage')->getCollection();
        $collection->addRejectedFilter()
                ->joinPatternNames();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
                                    'header'       => $this->__('ID'),
                                    'width'        => '100px',
                                    'index'        => 'id',
                                    'filter_index' => 'main_table.id',
                               ));

        $this->addColumn('from', array(
                                      'header' => $this->__('From'),
                                      'index'  => 'from',
                                      'filter_index' => 'main_table.from',
                                 ));

        $this->addColumn('subject', array(
                                         'header' => $this->__('Subject'),
                                         'index'  => 'subject'
                                    ));

        $this->addColumn('pattern_name', array(
                                              'header' => $this->__('Rejected by Pattern'),
                                              'index'  => 'pattern_name',
                                              'filter_index' => 'patterns.name',
                                         ));

        $this->addColumn('actions', array(
                                         'header'  => $this->__('Actions'),
                                         'width'   => '150px',
                                         'type'    => 'action',
                                         'getter'  => 'getId',
                                         'actions' => array(
                                             array(
                                                 'caption' => $this->__('Mark as unprocessed'),
                                                 'url'     => array('base' => '*/*/rejectedtounprocessed'),
                                                 'field'   => 'id'
                                             ),
                                             array(
                                                 'caption' => $this->__('Delete'),
                                                 'url'     => array('base' => '*/*/rejectedtoprocessed'),
                                                 'confirm' => $this->__('Are you sure you want do this?'),
                                                 'field'   => 'id'
                                             )
                                         ),
                                         'filter'    => false,
                                         'sortable'  => false,
                                         'is_system' => true
                                    ));
    }

    public function getGridRowUrl($row)
    {
        return null;
    }
}
