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

class AW_Helpdeskultimate_Model_System_Config_Backend_Status extends Mage_Core_Model_Config_Data
{
    /**
     * Retrives array of store id for selected scope
     * @return array
     */
    public function getStoreId()
    {
        $storeCode = Mage::app()->getRequest()->getParam('store');
        if ($storeCode) {
            return Mage::getModel('core/store')->load($storeCode)->getStoreId();
        }
        return 0;
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        $storeId = $this->getStoreId();
        $values = explode(",", $value);

        # 1. Save items we need
        $order = 1;
        $ids = array();
        foreach ($values as $value) {
            $value = trim($value);
            if ($value) {
                $status = Mage::getModel('helpdeskultimate/status');
                $status->load($value, 'label');
                if (!$status->getStatusId()) {
                    $status->setStoreId($storeId);
                    $status->setLabel($value);
                }
                $status->setLabel($value);
                $status->setOrdering($order);
                $status->save();
                $status->getResource()->setOrdering($status->getStatusId(), $order);
                $ids[] = $status->getStatusId();
                $order++;
            }
        }

        # 2. Get items for delete
        $statuses = Mage::getModel('helpdeskultimate/status')->getCollection();
        if (($storeId = $this->getStoreId()) !== null) {
            $statuses->addStoreFilter($storeId);
        }
        if (count($ids)) {
            $statuses->addFieldToFilter('status_id', array('nin' => $ids));
        }
        $deleteIds = $statuses->getAllIds();

        $deletedLabels = array();
        $undeletedLabels = array();
        foreach ($deleteIds as $id) {

            $status = Mage::getModel('helpdeskultimate/status')->load($id);
            if (!$status->getResource()->getUsedCount($id)) {
                $deletedLabels[] = $status->getLabel();
                $status->delete();
            } else {
                $undeletedLabels[] = $status->getLabel();
            }
        }

        if (count($undeletedLabels)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('helpdeskultimate')->__(
                    "Status(es) \"%s\" cannot be deleted because used in tickets",
                    implode("\", \"", $undeletedLabels)
                )
            );
        }

        if ($count = count($ids)) {
            $this->setValue($count);
        } else {
            $this->setValue(null);
        }

        return $this;
    }

    protected function _afterLoad()
    {
        $statuses = Mage::getModel('helpdeskultimate/status')->getCollection();
        if (($storeId = $this->getStoreId()) !== null) {
            $statuses->addStoreFilter($storeId);
        }
        $labels = array();
        foreach ($statuses as $status) {
            $labels[] = $status->getLabel();
        }
        $this->setValue(implode(", ", $labels));
        parent::_afterLoad();
    }
}