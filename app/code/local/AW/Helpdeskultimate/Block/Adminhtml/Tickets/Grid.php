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


class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    const XML_PATH_LAST_CUSTOMER_REPLY_ENABLED = 'helpdeskultimate/gridcolumns/last_customer_reply';
    const XML_PATH_LAST_DEPARTMENT_REPLY_ENABLED = 'helpdeskultimate/gridcolumns/last_department_reply';

    public function __construct()
    {
        parent::__construct();
        $this->setId('ticketsGrid');
        $this->setDefaultSort('created_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        if (
            is_null($this->getRequest()->getParam('filter', null))
            && is_null($this->getRequest()->getParam('sort', null))
            && !Mage::getSingleton('admin/session')->getData('aw_ticket_grid_set_is_default_filter_setted')
        ) {
            $this->setDefaultFilter(array('status' => '1'));
        } elseif (
            !is_null($this->getRequest()->getParam('sort', null))
            || !is_null($this->getRequest()->getParam('filter', null))
        ) {
            Mage::getSingleton('admin/session')->setData('aw_ticket_grid_set_is_default_filter_setted', true);
        }
        $this->setStoreSwitcherVisibility(true);
    }

    protected function _prepareCollection()
    {
        $t1 = Mage::getResourceModel('admin/user')->getTable('admin/user');
        $collection = Mage::getModel('helpdeskultimate/ticket')->getCollection();
        $collection->getSelect()
            ->joinLeft(
                array('u' => $t1), 'u.user_id=locked_by', array(
                    'locked_name' => 'CONCAT("by ", u.firstname," ",u.lastname," at ", locked_at)'
                )
            )
            ->columns(array('main_table.department_id AS ticket_department_id'));

        $collection->addStoreFilter($this->getRequest()->getParam('store'));

        $this->setCollection($collection);
        if ($this->_userMode && $this->getRequest()->getParam('id')) {
            $this->getCollection()->addFieldToFilter('main_table.customer_id', $this->getRequest()->getParam('id'));
        }
        if ($this->orderMode) {
            $this->getCollection()->addFieldToFilter('main_table.order_id', $this->getRequest()->getParam('order_id'));
        }
        if ($this->getCustomerEmailFilter()) {
            $this->getCollection()->addFieldToFilter('main_table.customer_email', $this->getCustomerEmailFilter());
        }
        $role = Mage::getSingleton('admin/session')->getUser()->getRole();
        $departmentPermissions = Mage::getModel('helpdeskultimate/department_permissions')
            ->loadByRoleId($role->getId());
        if (!is_null($departmentPermissions->getId())) {
            $value = $departmentPermissions->getValue();
            $this->getCollection()->addFieldToFilter('main_table.department_id', array('in' => $value));
        }
        return parent::_prepareCollection();
    }


    public function setFilterValues($data)
    {
        return $this->_setFilterValues($data);
    }

    protected function _getParams()
    {
        $params = array();
        if ($this->getCustomerEmailFilter()) {
            $params['ret'] = 'ticket';
            $params['touch'] = $this->getRequest()->getParam('id');
        }
        if ($store = $this->getRequest()->getParam('store')) {
            $params['store'] = $store;
        }
        if ($store = $this->getRequest()->getParam('filter')) {
            $params['filter'] = $store;
        }
        return $params;
    }

    protected function _prepareColumns()
    {

        $this->addColumn('uid', array(
            'header'           => $this->__('ID'),
            'align'            => 'right',
            'width'            => '50px',
            'index'            => 'uid',
            'column_css_class' => 'aw-hduat-ticket_uid'
        ));

        $this->addColumn('created_time', array(
            'header'           => $this->__('Created'),
            'align'            => 'left',
            'width'            => '140px',
            'index'            => 'created_time',
            'type'             => 'datetime',
            'column_css_class' => 'aw-hduat-ticket_created_time'
        ));

        $this->addColumn('last_reply', array(
            'header'           => $this->__('Last reply'),
            'align'            => 'left',
            'width'            => '140px',
            'index'            => 'last_reply',
            'type'             => 'datetime',
            'column_css_class' => 'aw-hduat-ticket_last_reply'
        ));

        if (Mage::getStoreConfig(self::XML_PATH_LAST_CUSTOMER_REPLY_ENABLED)) {
            $this->addColumn('last_customer_reply', array(
                'header'           => $this->__('Last customer reply'),
                'align'            => 'left',
                'width'            => '140px',
                'index'            => 'last_customer_reply',
                'type'             => 'datetime',
                'column_css_class' => 'aw-hduat-ticket_last_customer_reply'
            ));
        }

        if (Mage::getStoreConfig(self::XML_PATH_LAST_DEPARTMENT_REPLY_ENABLED)) {
            $this->addColumn('last_department_reply', array(
                'header'           => $this->__('Last department reply'),
                'align'            => 'left',
                'width'            => '140px',
                'index'            => 'last_department_reply',
                'type'             => 'datetime',
                'column_css_class' => 'aw-hduat-ticket_last_department_reply'
            ));
        }

        $options = Mage::getModel('helpdeskultimate/source_departments')->getFlatOptions();
        $role = Mage::getSingleton('admin/session')->getUser()->getRole();
        $departmentPermissions = Mage::getModel('helpdeskultimate/department_permissions')
            ->loadByRoleId($role->getId());
        if (!is_null($departmentPermissions->getId())) {
            foreach ($options as $key => $item) {
                if (!in_array($key, $departmentPermissions->getValue())) {
                    unset($options[$key]);
                }
            }
        }
        $this->addColumn('department_id', array(
            'header'           => $this->__('Department'),
            'index'            => 'department_id',
            'type'             => 'options',
            'options'          => $options,
            'column_css_class' => 'aw-hduat-ticket_department_id'
        ));

        $this->addColumn('title', array(
            'renderer'         => 'helpdeskultimate/adminhtml_widget_grid_column_renderer_title',
            'header'           => $this->__('Title'),
            'align'            => 'left',
            'width'            => '300px',
            'truncate'         => 100,
            'index'            => 'title',
            'column_css_class' => 'aw-hduat-ticket_title'
        ));

        if (!$this->_userMode) {
            $this->addColumn('customer_name', array(
                'header'           => $this->__('Customer'),
                'align'            => 'left',
                'index'            => 'customer_name',
                'column_css_class' => 'aw-hduat-ticket_customer_name'
            ));
        }

        $_filterIndex = 'CONCAT("by ", u.firstname," ",u.lastname," at ", locked_at)';
        $this->addColumn('locked_name', array(
            'renderer'                  => 'helpdeskultimate/adminhtml_widget_grid_column_renderer_lock',
            'header'                    => $this->__('Lock'),
            'align'                     => 'left',
            'index'                     => 'locked_name',
            'filter_index'              => $_filterIndex,
            'type'                      => 'options',
            'options'                   => Mage::getModel('helpdeskultimate/source_lock')->toOptionArray(),
            'column_css_class'          => 'aw-hduat-ticket_locked_name',
            'filter_condition_callback' => array($this, '_filterLock'),
        ));

        $this->addColumn('total_replies', array(
            'header'           => $this->__('Replies'),
            'align'            => 'left',
            'index'            => 'total_replies',
            'column_css_class' => 'aw-hduat-ticket_total_replies'
        ));

        $this->addColumn('status', array(
            'header'           => $this->__('Status'),
            'align'            => 'left',
            'width'            => '80px',
            'index'            => 'status',
            'type'             => 'options',
            'options'          => AW_Helpdeskultimate_Model_Status::getOptionArray(),
            'column_css_class' => 'aw-hduat-ticket_status'
        ));

        $this->getColumnRenderers();

        $this->addColumn('priority', array(
            'header'           => $this->__('Priority'),
            'align'            => 'left',
            'width'            => '80px',
            'index'            => 'priority',
            'renderer'         => 'helpdeskultimate/adminhtml_widget_grid_column_renderer_priority',
            'type'             => 'options',
            'options'          => Mage::getModel('helpdeskultimate/source_ticket_priority')->getOptionArray(),
            'column_css_class' => 'aw-hduat-priority'
        ));

        if (!$this->_userMode) {
            $this->addColumn('action',
                array(
                    'header'    => $this->__('Actions'),
                    'width'     => '40',
                    'type'      => 'action',
                    'getter'    => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => $this->__('Reply'),
                            'url'     => array('base' => '*/ticket/edit', 'params' => $this->_getParams()),
                            'field'   => 'id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
                ));

            $actions = array();
            $stores = Mage::getModel('core/store')->getCollection()->getAllIds();
            foreach ($stores as $storeId) {
                $actions[$storeId] = Mage::getSingleton('helpdeskultimate/status')->getOptionsArrayFor($storeId);
            }

            $this->addColumn('staction',
                array(
                    'header'    => $this->__('New Status'),
                    'width'     => '100',
                    'type'      => 'action',
                    'renderer'  => 'helpdeskultimate/adminhtml_widget_grid_column_renderer_statusaction',
                    'getter'    => 'getId',
                    'actions'   => $actions,
                    'props'     => array(
                        'url'   => array('base' => '*/index/customStatus', 'params' => $this->_getParams()),
                        'field' => 'id'
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
                ));

        }
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        if (!$this->_userMode) {
            $this->setMassactionIdField('main_table.id');
            $this->getMassactionBlock()->setFormFieldName('tickets');

            $this->getMassactionBlock()->addItem('delete', array(
                'label'   => $this->__('Delete'),
                'url'     => $this->getUrl('*/index/massDelete', $this->_getParams()),
                'confirm' => $this->__('Are you sure?')
            ));

            $this->getMassactionBlock()->addItem('assign', array(
                'label'      => $this->__('Assign'),
                'url'        => $this->getUrl(
                    '*/index/massAssign',
                    array_merge(array('_current' => true), $this->_getParams())
                ),
                'additional' => array(
                    'visibility' => array(
                        'name'   => 'assign',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => $this->__('Assign'),
                        'values' => Mage::getModel('helpdeskultimate/source_departments')->getFlatOptions()
                    )
                )
            ));

            $this->getMassactionBlock()->addItem('status', array(
                'label'      => $this->__('Change status'),
                'url'        => $this->getUrl(
                    '*/index/massStatus',
                    array_merge(array('_current' => true), $this->_getParams())
                ),
                'additional' => array(
                    'visibility' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => $this->__('Status'),
                        'values' => Mage::getSingleton('helpdeskultimate/status')->getOptionArray()
                    )
                )
            ));

            $this->getMassactionBlock()->addItem('locked', array(
                'label'      => $this->__('Lock'),
                'url'        => $this->getUrl('*/index/massLock', array_merge(array('_current' => true), $this->_getParams())),
                'additional' => array(
                    'visibility' => array(
                        'name'   => 'locked',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => $this->__('Locked (Yes/No)'),
                        'values' => array(
                            0 => $this->__('Unlock'),
                            1 => $this->__('Lock')
                        )
                    )
                )
            ));
        }
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('helpdeskultimate_admin/ticket/edit/', array('id' => $row->getId()));
    }

    protected function _filterLock($collection, $column)
    {
        $val = $column->getFilter()->getValue();

        if ($val) {
            $collection->getSelect()->where('locked_by > 0');
        } else {
            $collection->getSelect()->where('locked_by = 0');
        }

    }

    public function setOnePage()
    {
        $this->_defaultLimit = 0;
        return $this;
    }

    public function setUserMode($id = 1)
    {
        $this->_userMode = $id;
    }

    protected function _toHtml()
    {
        if ($this->getCustomerEmailFilter()) {
            return parent::_toHtml();
        } elseif ($this->getStoreSwitcherVisibility()) {
            $storeSwitcher = Mage::app()->getLayout()->createBlock('helpdeskultimate/adminhtml_store_switcher');
            if ($this->_userMode) {
                $_storeVarName = $storeSwitcher->getStoreVarName();
                $storeSwitcher->setData('switch_url',
                    $storeSwitcher->getUrl(
                        '*/*/*', array(
                            '_current'     => true,
                            $_storeVarName => null,
                            'active_tab'   => 'Tickets',
                        )
                    )
                );
            }
            return $storeSwitcher->toHtml() . parent::_toHtml();
        }
        return parent::_toHtml();
    }

    public function getGridUrl()
    {
        $params = array();
        if ($this->_userMode) {
            $params = array('active_tab' => 'Tickets');
        }
        return $this->getCurrentUrl($params);
    }
}
