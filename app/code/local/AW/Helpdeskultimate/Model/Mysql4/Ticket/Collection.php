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

class AW_Helpdeskultimate_Model_Mysql4_Ticket_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('helpdeskultimate/ticket');
    }

    public function addCustomerFilter($id)
    {
        $this->getSelect()->where('customer_id=?', $id);
        return $this;
    }

    public function orderBy($str)
    {
        $this->getSelect()
                ->order($str);
        return $this;
    }

    public function addStoreFilter($stores = null)
    {
        if (!$stores) return $this;
        $_stores = array(Mage::app()->getStore()->getId());
        if (is_string($stores)) $_stores = explode(',', $stores);
        if (is_array($stores)) $_stores = $stores;
        if (!$stores) return $this;
        $_sqlString = '(';
        $i = 0;
        foreach ($_stores as $_store) {
            $_sqlString .= sprintf('store_id = %s', $this->getConnection()->quote($_store));
            if (++$i < count($_stores))
                $_sqlString .= ' OR ';
        }
        $_sqlString .= ')';
        $this->getSelect()->where($_sqlString);

        return $this;
    }

    /**
     * Returns only active tickets(not closed)
     * @return AW_Helpdeskultimate_Model_Mysql4_Ticket_Collection
     */
    public function addActiveFilter()
    {
        $this->addFilter(
            'helpdeskultimate',
            $this->getConnection()->quoteInto('status!=?', AW_Helpdeskultimate_Model_Status::STATUS_CLOSED),
            'string'
        );
        return $this;
    }

    public function ___getSelectCountSql()
    {
        /* Covers original bug in Varien_Data_Collection_Db */
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        // $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        // $countSelect->reset(Zend_Db_Select::COLUMNS);
        // $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->from('', 'COUNT(uid)');
        //echo $countSelect->assemble();die();
        return $countSelect;
    }

    public function ___getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            $this->_totalRecords = count($this->getConnection()->fetchAll($sql, $this->_bindParams));
        }
        return intval($this->_totalRecords);
    }

    /**
     * Retrive all ids for collection
     *
     * @return array
     */
    public function ___getAllIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        // $idsSelect->reset(Zend_Db_Select::COLUMNS);
        // $idsSelect->reset(Zend_Db_Select::GROUP);
        $idsSelect->from(null, 'main_table.' . $this->getResource()->getIdFieldName());
        return $this->getConnection()->fetchCol($idsSelect);
    }

    /**
     * Attaches flat data to collection
     * @return AW_Sarp_Model_Mysql4_Subscription_Collection
     */
    protected function _initselect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            array('f' => Mage::getResourceModel('helpdeskultimate/ticket_flat')->getMainTable()),
            'f.ticket_id=main_table.id'
        );
        return $this;
    }
}
