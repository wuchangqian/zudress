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


class AW_Helpdeskultimate_Helper_Notify extends AW_Helpdeskultimate_Helper_Abstract
{

    /**
     * Runs when new ticket is created
     * @param object $ticket
     * @param object $customer [optional]
     * @return
     */
    public function ticketNew($ticket, $customer = null)
    {
        // If email notifications are not disabled, notify admin
        $department = $ticket->getDepartment();

        /*parse content for send into mail*/
        $originalContent = $ticket->getContent();
        $parsedContent = $this->_parseContent($ticket);
        $ticket->setContent(nl2br($parsedContent));
        $ticket->setContent($this->_processContent($ticket->getContent(), array(
            'ticket' => $ticket,
            'department' => $department
        )));
        if ($department->getEnabled() && $department->getNotify()) {
            // Notify admin/department
            $tpl = $department->getToAdminNewEmail();
            $sender = array('name' => $customer->getName(), 'email' => $customer->getEmail());
            $recipientEmail = $department->getContact();
            $recipientName = $department->getName();

            $mailTemplate = $this->_getMailTemplate($ticket, $department);
            if (!is_null($mailTemplate)) {
                $this->log('Sending mail about new ticket '
                    . $ticket->getUid()
                    . ' to department '
                    . $recipientName . '<' . $recipientEmail . '>'
                );
                $mailTemplate
                    ->setDesignConfig(array('area' => 'backend', 'store' => $ticket->getStoreId()))
                    ->sendTransactional(
                        $tpl,
                        $sender,
                        $recipientEmail,
                        $recipientName,
                        array('ticket' => $ticket, 'department' => $department),
                        $ticket->getStoreId()
                    );
            }
        }

        if ($customer) {
            $recipientEmail = is_string($customer) ? $customer : $customer->getEmail();
            $recipientName = is_string($customer) ? $customer : $customer->getName();
        } else {
            $recipientEmail = $ticket->getCustomerEmail();
            $recipientName = $ticket->getCustomerName();
        }

        if ($recipientEmail) {
            if ($ticket->getCreatedBy() == 'admin') {
                $tpl = $department->getNewFromAdminToCustomer();
            } else {
                $tpl = $department->getToCustomerNewEmail();
            }
            $sender = $department->getSender();

            $mailTemplate = $this->_getMailTemplate($ticket, $department);
            if (isset($mailTemplate) && !is_null($mailTemplate)) {
                $this->log('Sending mail about new ticket '
                    . $ticket->getUid()
                    . ' to customer '
                    . $recipientName . '<' . $recipientEmail . '>'
                );
                $mailTemplate
                    ->setDesignConfig(array('area' => 'frontend', 'store' => $ticket->getStoreId()))
                    ->sendTransactional(
                        $tpl,
                        $sender,
                        $recipientEmail,
                        $recipientName,
                        array('ticket' => $ticket, 'department' => $department),
                        $ticket->getStoreId()
                    );
            }
        }
        $ticket->setContent($originalContent);
    }

    /**
     * Sends notification about message to department is it tuned to receive notifications
     * @param AW_Helpdeskultimate_Model_Message $message
     * @return
     */
    public function ticketReplyToAdmin($message)
    {
        $ticket = $message->getTicket();
        $department = $ticket->getDepartment();

        $message = Mage::getModel('helpdeskultimate/message')->load($message->getId());

        /*parse content for send into mail*/
        $originalContent = $message->getContent();
        $parsedContent = $this->_parseContent($message);
        $message->setContent($parsedContent);
        $message->setContent($this->_processContent($message->getContent(), array(
            'ticket' => $ticket,
            'department' => $department,
            'message' => $message,
        )));
        if ($department->getEnabled() && $department->getNotify()) {
            // Notify admin
            $tpl = $department->getToAdminReplyEmail();
            $sender = $department->getSender();
            $recipientEmail = $department->getContact();
            $recipientName = $department->getName();

            $mailTemplate = $this->_getMailTemplate($message, $department);
            if (!is_null($mailTemplate)) {
                $this->log('Sending mail about reply in ticket '
                    . $ticket->getUid()
                    . ' to department '
                    . $recipientName . '<' . $recipientEmail . '>'
                );
                $mailTemplate
                    ->setDesignConfig(array('area' => 'backend'))
                    ->sendTransactional(
                        $tpl,
                        $sender,
                        $recipientEmail,
                        $recipientName,
                        array('ticket' => $ticket, 'message' => $message),
                        $ticket->getStoreId()
                    );
            }
        }
        $message->setContent($originalContent);
    }


    public function ticketReassigned($ticket)
    {
        // If email notifications are not disabled, notify admin
        $newDepartment = $ticket->getDepartment();
        /*parse content for send into mail*/
        $originalContent = $ticket->getContent();
        $parsedContent = $this->_parseContent($ticket);
        $ticket->setContent($parsedContent);        

        $mailTemplate = $this->_getMailTemplate($ticket, $newDepartment);
        $storeId = $ticket->getStoreId();
        $sendReassign = Mage::getStoreConfig('helpdeskultimate/advanced/email_customer_reassign', $storeId);
        /* send reassign email notification to customer */
        if ($sendReassign && $mailTemplate) {
            if ($newDepartment->getToCustomerReassignEmail()) {
                $templateId = $newDepartment->getToCustomerReassignEmail();
            } else {
                $templateId = Mage::getStoreConfig('helpdeskultimate/generaldep/to_customer_reassign_email', $storeId);
            }
            $mailTemplate
                ->setDesignConfig(array('area' => 'backend'))
                ->sendTransactional(
                    $templateId,
                    $newDepartment->getSender(),
                    $ticket->getCustomer()->getEmail(),
                    $ticket->getCustomer()->getName(),
                    array('ticket' => $ticket, 'newdepartment' => $newDepartment, 'customer' => $ticket->getCustomer()),
                    $ticket->getStoreId()
                );
        }
         /* send reassign email notification to department */      
        if ($newDepartment->getEnabled() && $newDepartment->getNotify()) { 
            $recipientEmail = $newDepartment->getContact();
            $recipientName = $newDepartment->getName();             
            if (!is_null($mailTemplate)) {
                $this->log('Sending mail about reassigned ticket '
                    . $ticket->getUid()
                    . ' to department '
                    . $recipientName . '<' . $recipientEmail . '>'
                );
                if ($newDepartment->getToAdminReassignEmail()) {
                    $templateId = $newDepartment->getToAdminReassignEmail();
                } else {
                    $templateId = Mage::getStoreConfig('helpdeskultimate/generaldep/to_admin_reassign_email', $storeId);
                }
                $mailTemplate
                    ->setDesignConfig(array('area' => 'backend'))
                    ->sendTransactional(
                        $templateId,
                        $newDepartment->getSender(),
                        $recipientEmail,
                        $recipientName,
                        array('ticket' => $ticket, 'newdepartment' => $newDepartment),
                        $ticket->getStoreId()
                    );
            }
        }
       
        $ticket->setContent($originalContent);
    }

    public function ticketReplyToCustomer($message, $customer = null)
    {
        $message = Mage::getModel('helpdeskultimate/message')->load($message->getId());
        $ticket = $message->getTicket();
        $department = $ticket->getDepartment();
        if (is_null($customer)) {
            $customer = $ticket->getCustomer();
        }

        /*parse content for send into mail*/
        $originalContent = $message->getContent();
        $parsedContent = $this->_parseContent($message);
        $message->setContent($parsedContent);
        $message->setContent($this->_processContent($message->getContent(), array(
            'ticket' => $ticket,
            'department' => $department,
            'message' => $message
        )));

        $tpl = $department->getToCustomerReplyEmail();
        $sender = $department->getSender();
        $recipientEmail = $customer->getId() ? $customer->getEmail() : $message->getTicket()->getCustomerEmail();
        $recipientName = $customer->getId() ? $customer->getName() : $message->getTicket()->getCustomerName();

        $mailTemplate = $this->_getMailTemplate($message, $department);
        if (!is_null($mailTemplate)) {
            $this->log("Sending mail about reply in {$ticket->getUid()} to customer $recipientName<$recipientEmail>");
            $mailTemplate
                ->setDesignConfig(array('area' => 'frontend', 'store' => $ticket->getStoreId()))
                ->sendTransactional(
                    $tpl,
                    $sender,
                    $recipientEmail,
                    $recipientName,
                    array('ticket' => $ticket, 'message' => $message, 'department' => $department),
                    $ticket->getStoreId()
                );
        }
        $message->setContent($originalContent);
    }

    protected function _parseContent($message)
    {
        $isStripTagAttributes = true;
        if (Mage::helper('helpdeskultimate/parser')->isDepartmentAuthor($message)) {
            $_parser = Mage::getModel('helpdeskultimate/data_parser')->getInstance('html');
            $isStripTagAttributes = false;
        } else {
            $_parser = Mage::getModel('helpdeskultimate/data_parser')->getInstance('text');
        }
        $_parser->setText($message->getParsedContent());
        $_parser->prepareToDisplay($isStripTagAttributes);
        return $_parser->getText();
    }

    protected function _processContent($text, array $vars)
    {
        $tf = Mage::getModel('core/email_template_filter');
        $tf
            ->setUseAbsoluteLinks(true)
            ->setVariables($vars);

        try {
            return $tf->filter($text);
        }
        catch (Exception $e) {
            $this->log("Error occured while parsing text: {$e->getMessage()}");
        }
    }

    /*$object = ticket | message*/
    protected function _getMailTemplate($object, $department)
    {
        if (
            !$object instanceof AW_Helpdeskultimate_Model_Ticket
            && !$object instanceof AW_Helpdeskultimate_Model_Message
        ) {
            return null;
        }
        if (!$department instanceof AW_Helpdeskultimate_Model_Department) {
            return null;
        }

        $mailTemplate = Mage::getModel('core/email_template');
        //set carbon copy
        $mailTemplate = $this->_setCarbonCopy($mailTemplate);
        //adding attachments
        $this->_processAttachments($mailTemplate->getMail(), $object->getFolderName(), $object->getFilename());
        //set reply-to
        $_replyTo = $this->_getReplyToByDepartment($department);
        $mailTemplate = $this->_setReplyTo($mailTemplate, $_replyTo);
        return $mailTemplate;
    }

    private function _getReplyToByDepartment($department)
    {
        if ($department && $department->getMainGateway()) {
            $replyTo = $department->getMainGateway()->getEmail();
        } else {
            $replyTo = Mage::getStoreConfig('trans_email/ident_general/email');
            $this->log("Cannot get Reply To Email");
        }
        return $replyTo;
    }

    private function _processAttachments($mail, $folderName, $fileNames)
    {
        if ($fileNames) {
            foreach ($fileNames as $filename) {
                $realFilename = Mage::helper('helpdeskultimate')->getEncodedFileName($filename);
                $at = $mail->createAttachment(file_get_contents($folderName . $realFilename));
                $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $at->encoding = Zend_Mime::ENCODING_BASE64;
                $at->filename = $filename;
            }
        }
    }

    private function _setCarbonCopy($mailTemplate)
    {
        if ($copyTo = Mage::helper('helpdeskultimate')->getCarbonCopy())
            $mailTemplate->addBcc($copyTo);
        return $mailTemplate;
    }

    private function _setReplyTo($mailTemplate, $replyTo)
    {
        try {
            $mailTemplate->setReplyTo($replyTo);
        } catch (Exception $e) {
            $mailTemplate->getMail()->setReplyTo($replyTo);
        }
        return $mailTemplate;
    }

    private function log()
    {
        $args = func_get_args();
        call_user_func_array(array(Mage::helper('helpdeskultimate/logger'), 'log'), array_values($args));
        return $this;
    }
}
