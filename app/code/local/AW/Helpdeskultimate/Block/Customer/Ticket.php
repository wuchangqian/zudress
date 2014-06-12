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

class AW_Helpdeskultimate_Block_Customer_Ticket extends Mage_Customer_Block_Account_Dashboard
{
    const TEMPLATE_PATH = "helpdeskultimate/ticket.phtml";
    public function __construct()
    {
        $this->setTemplate(self::TEMPLATE_PATH);
        $ticketModel = Mage::getModel('helpdeskultimate/ticket');
        if ($this->getRequest()->getParam('id', false)) {
            $ticketModel->load($this->getRequest()->getParam('id', false));
        }
        $this->setTicket($ticketModel);
        $this->_customer = Mage::getSingleton('customer/session')->getCustomer();
    }

    public function dateFormat($date)
    {
        $_dateFormat = ""
                     . $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
                     . " "
                     . $this->formatTime($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        return $_dateFormat;
    }

    public function getOrder()
    {
        if (!is_null($this->getTicket()->getOrder()->getId())) {
            return $this->getTicket()->getOrder();
        }
        return null;
    }

    /*getting data from ticket as block. Ex: $block->getOrder() ~ $this->getTicket()->getOrder()*/
    public function getData($key='', $index=null)
    {
        $_indexes = array(
            'title', 'uid',
            'status_text',
            'department',
            'created_time',
            'filename',
            'file_url',
            'customer_name',
            'order',
        );
        if (in_array($key, $_indexes)) {
            $method = 'get' . $this->_camelize($key, '');
            return call_user_func(array($this->getTicket(), $method));
        }
        return parent::getData($key, $index);
    }

    public function getContent()
    {
        $cnt = $this->getTicket()->getContent();
        $cnt = Mage::getModel('helpdeskultimate/data_parser')->setText($cnt)->prepareToDisplay()->getText();
        return $cnt;
    }

    public function getCustomer()
    {
        return $this->_customer;
    }

    public function getTicketId()
    {
        return $this->getTicket()->getHelpdeskMessageId();
    }

    public function getTicketTitle()
    {
        return $this->getTicket()->getTitle();
    }

    public function getBackUrl()
    {
        return Mage::getUrl('helpdeskultimate/customer/');
    }

}
