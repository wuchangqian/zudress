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

class AW_Helpdeskultimate_Model_System_Config_Backend_Statusbyadmin extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        $value = trim($this->getValue());
        $status = Mage::getModel('helpdeskultimate/status');

        if ($status->getResource()->statusExist($value)) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('helpdeskultimate')->__(
                        "Status \"%s\" cannot be added because status with the same name is already exists!",
                        $value
                    )
                );
            return;
        }

        $status->load('admin', 'status_type');
        if ($value != '') {
            if ($status->getId()) {
                $status->setLabel($value);
            } else {
                $newStatus = array(
                    'label' => $value,
                    'status_type' => 'admin',
                );
                $status->setData($newStatus);
            }
            $status->save();
        } else {
            if (!$status->getResource()->getUsedCount($status->getId())) {
                $status->delete();
                /*
                if ($status->getLabel()) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('helpdeskultimate')->__(
                            "Status(es) \"%s\" was successfully deleted",
                            $status->getLabel()
                        )
                    );
                }
                */
            } else {
                Mage::getSingleton('adminhtml/session')
                    ->addError(
                        Mage::helper('helpdeskultimate')->__(
                            "Status(es) \"%s\" cannot be deleted because used in tickets",
                            $status->getLabel()
                        )
                    );
            }
        }
    }

    protected function _afterLoad()
    {
        $status = Mage::getModel('helpdeskultimate/status')->load('admin', 'status_type');
        $this->setValue($status->getLabel());
        parent::_afterLoad();
    }

}