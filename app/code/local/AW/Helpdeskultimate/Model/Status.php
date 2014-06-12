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

/**
 * Ticket Statuses Model
 */
class AW_Helpdeskultimate_Model_Status extends AW_Core_Model_Abstract
{
    /**
     * Status Open
     */
    const STATUS_OPEN = 1;

    /**
     * Status closed
     */
    const STATUS_CLOSED = 2;

    /**
     * Status Wait to customer
     */
    const STATUS_WAITING = 3;

    /**
     * Class constructor
     */
    protected function _construct()
    {
        $this->_init('helpdeskultimate/status');
    }

    public function getOptionsArrayFor($storeId = 0)
    {
        $statuses = array(
            self::STATUS_OPEN => Mage::helper('helpdeskultimate')->__('Open'),
            self::STATUS_CLOSED => Mage::helper('helpdeskultimate')->__('Closed'),
            self::STATUS_WAITING => Mage::helper('helpdeskultimate')->__('Waiting for customer')
        );

        $globalStatuses = Mage::getModel('helpdeskultimate/status')->getCollection();
        $globalStatuses->addPublicStoreFilter();
        foreach ($globalStatuses as $status) {
            $statuses[$status->getId()] = $status->getLabel();
        }
        $localStatuses = Mage::getModel('helpdeskultimate/status')->getCollection();
        if ($storeId) {
            $localStatuses->addStoreFilter($storeId);
        } else {
            $localStatuses->setOrder('store_id', 'ASC');
        }
        foreach ($localStatuses as $status) {
            $statuses[$status->getId()] = $status->getLabel();
        }
        return $statuses;
    }

    /**
     * Array of statuses
     * @return array
     */
    static public function getOptionArray()
    {
        $statuses = array(
            self::STATUS_OPEN => Mage::helper('helpdeskultimate')->__('Open'),
            self::STATUS_CLOSED => Mage::helper('helpdeskultimate')->__('Closed'),
            self::STATUS_WAITING => Mage::helper('helpdeskultimate')->__('Waiting for customer')
        );

        $globalStatuses = Mage::getModel('helpdeskultimate/status')->getCollection();
        $globalStatuses->addPublicStoreFilter();
        foreach ($globalStatuses as $status) {
            $statuses[$status->getId()] = $status->getLabel();
        }
        $localStatuses = Mage::getModel('helpdeskultimate/status')->getCollection();
        if ($store = Mage::app()->getRequest()->getParam('store')) {
            $localStatuses->addStoreFilter($store);
        } else {
            $localStatuses->setOrder('store_id', 'ASC');
        }
        foreach ($localStatuses as $status) {
            $statuses[$status->getId()] = $status->getLabel();
        }
        return $statuses;
    }

    /**
     * Returns option text by key
     * @param object $id
     * @return
     */
    static public function getOption($id)
    {
        $arr = self::getOptionArray();
        return @$arr[$id];
    }

    /**
     * Returns array with options
     * @return array
     */
    public function getAllOptions()
    {
        $arr = array();
        foreach ($this->getOptionArray() as $k => $v) {
            $arr[] = array('label' => $v, 'value' => $k);
        }
        return $arr;
    }

    /**
     *
     * @param integer $statusId
     * @return string
     */
    public function getStatusLabel($statusId)
    {
        foreach ($this->getOptionArray() as $k => $v) {
            if ($k == $statusId) {
                return $v;
            }
        }
        return '';
    }

    /**
     * Retrives allowed flag
     * @param integer $storeId
     * @param integer $statusId
     * @return boolean
     */
    public function isAllowToSet($storeId, $statusId)
    {
        foreach ($this->getOptionsArrayFor($storeId) as $k => $v) {
            if ($k == $statusId) {
                return true;
            }
        }
        return false;
    }

    protected function _afterSave()
    {
        $id = $this->getData('status_id');
        if ($id < 4) {
            $this->setData('status_id', $id + 3);
            $this->save();
        }
    }

}
