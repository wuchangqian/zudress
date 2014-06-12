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

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tab_Thread extends Mage_Adminhtml_Block_Template
{
    protected $_mode;

    public function __construct()
    {
        parent::__construct();
        $this->setId('helpdeskMessagesGrid');
        $this->setTemplate('helpdeskultimate/ticket/edit/thread.phtml');
        $this->_label = Mage::helper('helpdeskultimate')->__("Ticket Thread");
        $this->_headLabel = Mage::helper('helpdeskultimate')->__("Ticket Information");
    }

    public function setDisplayMode($mode)
    {
        $this->_mode = $mode;
        return $this;
    }

    public function getRemoveMessageUrl($ticketId, $messageId)
    {
        return $this->getUrl('*/*/removemessage', array('id' => $ticketId, 'mid' => $messageId));
    }

    public function getSaveMessageBodyUrl()
    {
        return $this->getUrl('*/*/savemessagebody');
    }

    public function getParserForMessage($message)
    {
        if ($this->isDepartmentAuthor($message)) {
            $_instance = 'html';
        } else {
            $_instance = 'text';
        }
        return Mage::getModel('helpdeskultimate/data_parser')->getInstance($_instance);
    }

    public function isDepartmentAuthor($message)
    {
        if ($message instanceof AW_Helpdeskultimate_Model_Message && $message->isDepartmentReply()) {
            return true;
        } elseif ($message instanceof AW_Helpdeskultimate_Model_Ticket && $message->getCreatedBy() == 'admin') {
            /* check on html */
            $text = $message->getOrigData('content');
            if (strip_tags($text) == $text) {
                /* if not html then it is email (plain/text)*/
                return false;
            }
            return true;
        }
        return false;
    }

    protected function _getAuthorHtml($name = null)
    {
        $ticket = $this->getCollection()->getTicket();
        return $name ? $name : $ticket->getInitiator()->getName();
    }

    function insertUrls($text)
    {
        return Mage::helper('helpdeskultimate/threads')->processLinks($text);
    }

    /**
     * Formats date
     * @param string $dt
     * @return string
     */
    protected function DTFormat($dt)
    {
        return $this->formatDate($dt, 'medium', true);
    }


}
