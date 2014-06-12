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

class AW_Helpdeskultimate_Model_Ticket_Flat extends AW_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('helpdeskultimate/ticket_flat');
    }

    /**
     * Presets all ticket info
     * @param AW_Helpdeskultimate_Model_Ticket $ticket
     * @return AW_Helpdeskultimate_Model_Ticket_Flat
     */
    public function setTicket(AW_Helpdeskultimate_Model_Ticket $ticket)
    {
        $lastReplyDate = null;
        $customerReplyProcessed = $departmentReplyProcessed = 0;
        foreach ($ticket->getMessages()->setOrder('created_time', 'desc') as $message) {
            if (!$lastReplyDate) {
                $lastReplyDate = new Zend_Date(
                    $message->getCreatedTime(),
                    AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT
                );
            }
            if ($message->isDepartmentReply() && !$departmentReplyProcessed) {
                $this->setLastDepartmentReply($message->getCreatedTime());
                $departmentReplyProcessed = 1;
            }
            if (!$message->isDepartmentReply() && !$customerReplyProcessed) {
                $this->setLastCustomerReply($message->getCreatedTime());
                $customerReplyProcessed = 1;
            }
            if ($customerReplyProcessed && $departmentReplyProcessed) {
                break;
            }
        }

        $this
                ->setTicketId($ticket->getId())
                ->setLastReply($lastReplyDate)
                ->setTotalReplies($ticket->getMessagesCount());
        $ticket
                ->setLastReply($this->getLastReply())
        //->setTotalReplies($this->getTotalReplies());
                ->setTotalReplies($ticket->getMessagesCount());
        return $this;
    }


    /**
     * Returns saved last reply date
     * @return Zend_Date
     */
    public function getLastReply()
    {
        return new Zend_Date($this->getData('last_reply'), AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT);
    }

    /**
     * Converts last reply from date to string
     * @return Zend_Date
     */
    public function setLastReply($date)
    {
        if ($date instanceof Zend_Date) {
            $date = $date->toString(AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT);
        }
        $this->setData('last_reply', $date);
        return $this;
    }

    /**
     * Returns saved last reply date
     * @return Zend_Date
     */
    public function getLastDepartmentReply()
    {
        return new Zend_Date(
            $this->getData('last_department_reply'),
            AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT
        );
    }

    /**
     * Converts last reply from date to string
     * @return Zend_Date
     */
    public function setLastDepartmentReply($date)
    {
        if ($date instanceof Zend_Date) {
            $date = $date->toString(AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT);
        }
        $this->setData('last_department_reply', $date);
        return $this;
    }


    /**
     * Returns saved last reply date
     * @return Zend_Date
     */
    public function getLastCustomerReply()
    {
        return new Zend_Date(
            $this->getData('last_customer_reply'),
            AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT
        );
    }

    /**
     * Converts last reply from date to string
     * @return Zend_Date
     */
    public function setLastCustomerReply($date)
    {
        if ($date instanceof Zend_Date) {
            $date = $date->toString(AW_Helpdeskultimate_Model_Ticket::DB_DATETIME_FORMAT);
        }
        $this->setData('last_customer_reply', $date);
        return $this;
    }

    public function loadByTicketId($ticketId)
    {
        return $this->load($ticketId, 'ticket_id');
    }
}
