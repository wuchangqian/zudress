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

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tab_Status extends Mage_Adminhtml_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('helpdeskultimate/ticket/edit/status.phtml');
    }

    /**
     * Returns status changer name
     * @param object $entry
     * @return
     */
    public function getChangerName($entry)
    {
        try {
            if ($entry->getData('custom_field_4')) {
                // Changed by customer
                $ticketId = $entry->getData('custom_field_1');
                return '[C] ' . Mage::getModel('helpdeskultimate/ticket')->load($ticketId)->getCustomer()->getName();
            } else {
                $departmentId = $entry->getData('custom_field_3');
                return '[D] ' . Mage::getModel('helpdeskultimate/department')->load($departmentId)->getName();

            }
        } catch (Exception $e) {
            return $this->__('Unknown department/customer');
        }

    }


    public function getTicket()
    {
        return Mage::registry('hdu_ticket_data');
    }

    public function getCollection()
    {
        $coll = Mage::getModel('awcore/logger')->getCollection();
        $coll->getSelect()
                ->where("code='AW_HDU_STATUS_CH' and custom_field_1={$this->getTicket()->getId()}");
        $coll->setOrder('date', 'DESC');
        return $coll;
    }

}