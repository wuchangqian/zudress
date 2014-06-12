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

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tab_Additional extends Mage_Adminhtml_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('helpdeskultimate/ticket/edit/additional.phtml');
    }

    public function isAssigned()
    {
        return !!$this->getTicket()->getCustomerId();
    }

    public function getAssignedHtml()
    {
        $customerId = $this->getTicket()->getCustomerId();
        $href = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/customer/edit', array('id' => $customerId));
        $title = $this->getTicket()->getCustomer()->getName();
        return '<a href="' . $href . '">' . $title . '</a>';
    }

    public function getTicket()
    {
        return Mage::registry('hdu_ticket_data');
    }

    public function isOrderAssigned()
    {
        return !!$this->getTicket()->getOrderId() && Mage::getStoreConfig('helpdeskultimate/advanced/orders_enabled');
    }

    public function getAssignedOrderHtml()
    {
        $href = $this->getUrl('adminhtml/sales_order/view', array('order_id' => $this->getTicket()->getOrderId()));
        $title = $this->getTicket()->getOrder()->getIncrementId();
        $status = $this->getTicket()->getOrder()->getStatusLabel();
        return '<a href="' . $href . '">#' . $title . '</a>&nbsp;&nbsp;&nbsp;(' . $status . ')';
    }

}
