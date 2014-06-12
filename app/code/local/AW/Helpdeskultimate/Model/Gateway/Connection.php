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

class AW_Helpdeskultimate_Model_Gateway_Connection extends Varien_Object
{
    const IMAP_ENGINE_CLASS = 'AW_Helpdeskultimate_Model_Gateway_Connection_Imap';
    const POP_ENGINE_CLASS = 'AW_Helpdeskultimate_Model_Gateway_Connection_Pop3';

    /**
     * Initializes service from gateway
     * @param Varien_Object $gateway Object with all connection params
     * @return
     */
    public function initFromVarienObject(Varien_Object $gateway)
    {
        $this
            ->setType($gateway->getProtocol())
            ->setHost($gateway->getHost())
            ->setLogin($gateway->getLogin())
            ->setPassword($gateway->getPassword())
            ->setPort($gateway->getPort())
            ->setSecure($this->_getGatewaySecure($gateway));

        $instanceConstructor = $this->_getConnectionConstructor();
        try {
            // Try to connect
            $this->setInstance(new $instanceConstructor($this->_getConnectionParams()));
        } catch (Zend_Mail_Protocol_Exception $e) {
            $this->log($e->getMessage());
            return $e->getMessage();
        }
        return true;
    }

    /**
     * Initializes service from gateway
     * @param AW_Helpdeskultimate_Model_Gateway $gateway
     * @return
     */
    public function initFromGateway(AW_Helpdeskultimate_Model_Gateway $gateway)
    {
        $this
            ->setType($gateway->getProtocol())
            ->setHost($gateway->getHost())
            ->setCreateTickets($gateway->getCreateTickets())
            ->setLogin($gateway->getLogin())
            ->setGatewayId($gateway->getId())
            ->setDeleteMessages($gateway->getDeleteMessage())
            ->setPassword($gateway->getPassword())
            ->setPort($gateway->getPort())
            ->setSecure($this->_getGatewaySecure($gateway))
            ->setGateway($gateway);

        $instanceConstructor = $this->_getConnectionConstructor();
        try {
            // Try to connect
            $this->setInstance(new $instanceConstructor($this->_getConnectionParams()));
        } catch (Zend_Mail_Protocol_Exception $e) {
            $this->log($e->getMessage());
            return false;
        }
        return $this;
    }

    /**
     * Returns parameters for connection
     * @return array
     */
    protected function _getConnectionParams()
    {
        $params = array(
            'host' => $this->getHost(),
            'user' => $this->getLogin(),
            'password' => $this->getPassword()
        );
        if ($this->getPort()) {
            $params['port'] = $this->getPort();
        }
        if ($this->getSecure()) {
            $params['ssl'] = strtoupper($this->getSecure());
        }
        return $params;
    }

    /**
     * Returns new UIDs from mailbox
     * @return array
     */
    public function getNewUIDs()
    {
        if (!$this->getData('new_uids')) {
            $newUIDs = $existingUIDs = array();
            // Get all uids from mailbox
            $mailboxUIDs = $this->getInstance()->getUniqueId();

            $existingUIDs = Mage::getModel('helpdeskultimate/popmessage')->getCollection()
                ->addGatewayIdFilter($this->getGatewayId())
                ->addFieldToSelect('uid')
                ->getColumnValues('uid');
            $newUIDs = array_diff($mailboxUIDs, $existingUIDs);
            $this->setData('new_uids', $newUIDs);
        }
        return $this->getData('new_uids');
    }

    /**
     * Returns new messages at connection
     * @return
     */
    public function saveNewMessages()
    {
        $newUids = $this->getNewUIDs();
        $this->log("%d new messages found", $this->getStats()->getCountMessages());
        $_mailCounter = 0;
        foreach ($newUids as $uid) {
            $this->log("(%d) message UID: \"%s\"", ++$_mailCounter, $uid);
            Mage::helper('helpdeskultimate/logger')->addTab();
            try {
                $number = $this->getInstance()->getNumberByUniqueId($uid);
                $_internalCharset = iconv_get_encoding('internal_encoding');
                iconv_set_encoding('internal_encoding', AW_Helpdeskultimate_Helper_Config::STORAGE_ENCODING);
                $message = $this->getInstance()->getMessage($number);
                iconv_set_encoding('internal_encoding', $_internalCharset);
                /* Save to DB as not processed */

                $status = AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection::STATUS_UNPROCESSED;
                $popmessage = Mage::getModel('helpdeskultimate/popmessage')
                    ->setUid($uid)
                    ->setFrom($message->from)
                    ->setTo($this->getGateway()->getEmail())
                    ->setContentType($this->getContentType($message))
                    ->setDate(now())
                    ->setGatewayId($this->getGatewayId())
                    ->setHeaders($this->getInstance()->getRawHeader($number))
                    ->setBody($this->getMessageBody($message))
                    ->setStatus($status);

                if ((method_exists($message, 'headerExists') && $message->headerExists('subject'))
                    || (!method_exists($message, 'headerExists') && array_key_exists('subject', $message->getHeaders()))
                ) {
                    $popmessage->setSubject($message->subject);
                } else {
                    $popmessage->setSubject(Mage::helper('helpdeskultimate')->__('No Subject'));
                }
                $this->log("Content-Type: %s", $popmessage->getContentType());
                $this->log("Subject: %s", $popmessage->getSubject());

                $attachments = Mage::helper('helpdeskultimate/config')->isAllowedManageFiles()
                    ? ($this->getAttachment($message)) : null;
                if ($attachments) {
                    $_attachments = array();
                    $_attachmentNames = array();
                    foreach ($attachments as $attachment) {
                        $_uploadMaxFileSize = Mage::helper('helpdeskultimate')->getUploadMaxFileSize();
                        if (strlen($attachment['content']) / 1024 / 1024 > $_uploadMaxFileSize) {
                            $this->log(
                                "Attachment can't be saved because \"%s\" is too large",
                                $attachment['filename']
                            );
                        } else {
                            $this->log("Got attachment \"%s\"", $attachment['filename']);
                            $attachment['content'] = @base64_encode($attachment['content']);
                            $_attachments[] = $attachment;
                            $_attachmentNames[] = preg_replace(
                                '/([\/*:\?<>\| \\\"\'])+/i',
                                '-',
                                $attachment['filename']
                            );
                        }
                    }
                    if ($_attachments)
                        $popmessage->setAttachmentName(implode('|', $_attachmentNames));
                    $attachmentDB = Mage::getModel('helpdeskultimate/attachment');
                    $attachmentDB->setData(array(
                        'uid' => $uid,
                        'attachments' => $_attachments
                    ))->save();
                }
                //chekc via reject pattern
                if (($rejPid = Mage::helper('helpdeskultimate/imap')->matchRejectingPatterns($popmessage))) {
                    $popmessage->setStatus(AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection::STATUS_REJECTED);
                    $popmessage->setRejPid($rejPid);
                }
                $popmessage->save();

                //Logging  about new message or rejecting
                $popMessageStatus = $popmessage->getStatus();
                if ($popMessageStatus == AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection::STATUS_REJECTED) {
                    $this->log("Message (ID: %s) has been rejected (pattern #%s)", $popmessage->getId(), $rejPid);
                } else {
                    $this->log("New message (ID: %s) from %s", $popmessage->getId(), $popmessage->getFrom());
                }
            } catch (Exception $e) {
                try {
                    $_from = $message->from;
                } catch (Exception $e) {
                    $_from = 'Unknown';
                }
                $this->log('Bad message from %s: %s', $_from, $e->getMessage());
            }
            Mage::helper('helpdeskultimate/logger')->removeTab();
        }

        return $this;
    }

    public function deleteFromRemoteServerByUid($uid)
    {
        $number = $this->getInstance()->getNumberByUniqueId($uid);
        $this->getInstance()->removeMessage($number);
        return $this;
    }

    /**
     * Returns wich engine to use
     * @return string
     */
    protected function _getConnectionConstructor()
    {
        if (strtolower($this->getType()) == 'imap') {
            return self::IMAP_ENGINE_CLASS;
        } else {
            return self::POP_ENGINE_CLASS;
        }
    }

    public function getAttachment($message, $dsata = null)
    {
        $data = array();
        // Get first flat part

        if ($message->isMultipart()) {
            $parts = $message;
            foreach (new RecursiveIteratorIterator($parts) as $part) {
                $attach = $this->getAttachment($part, $data);
                if ($attach)
                    $data[] = $attach;
            }
        } else {
            $headers = $message->getHeaders();
            $isAttachment = null;
            foreach ($headers as $name => $value) {
                if (is_array($value)) {
                    $value = implode(";", $value);
                }
                if ($isAttachment = preg_match('/(name|filename)="{0,1}([^;\"]*)"{0,1}/si', $value, $matches)) {
                    break;
                }
            }
            if ($isAttachment) {
                $filename = $matches[2];
                $encodedContent = $message->getContent();

                // Decoding transfer-encoding
                switch ($transferEncoding = @$headers['content-transfer-encoding']) {
                    case Zend_Mime::ENCODING_QUOTEDPRINTABLE:
                        $content = quoted_printable_decode($encodedContent);
                        break;
                    case Zend_Mime::ENCODING_BASE64:
                        $content = base64_decode($encodedContent);
                        break;
                    default:
                        $content = $encodedContent;
                }

                $filename = iconv_mime_decode(
                    $filename,
                    ICONV_MIME_DECODE_CONTINUE_ON_ERROR,
                    AW_Helpdeskultimate_Helper_Config::STORAGE_ENCODING
                );
                return array('filename' => $filename, 'content' => $content);
            } else {
                return false;
            }
        }
        return $data;
    }

    /**
     * Returns main mail part
     * @param Zend_Mail_Message $message
     * @return Zend_Mail_Message
     */
    protected function _getMainPart(Zend_Mail_Message $message)
    {
        // Get first flat part
        $part = $message;
        while ($part->isMultipart()) {
            $part = $part->getPart(1);
        }
        return $part;
    }


    /**
     * Returns first part of mail/mailpart content-type. It can be text/html or text/plain and so on
     * @param Zend_Mail_Message $message
     * @return string
     */
    public function getContentType(Zend_Mail_Message $message)
    {
        $part = $this->_getMainPart($message);
        try {
            $headers = $part->getHeaders();
            $contentType = @$headers['content-type'] ? $headers['content-type']
                : AW_Helpdeskultimate_Helper_Config::DEFAULT_MIME_TYPE;
        } catch (Exception $e) {
            $contentType = AW_Helpdeskultimate_Helper_Config::DEFAULT_MIME_TYPE;
        }
        return strtok($contentType, ';');
    }

    /**
     * Fetches first not multi-part data and decodes it according to headers information
     * @param Zend_Mail_Message $message
     * @return string
     */
    public function getMessageBody(Zend_Mail_Message $message)
    {
        // Get first flat part
        $part = $this->_getMainPart($message);

        $headers = $part->getHeaders();
        $encodedContent = $part->getContent();

        // Decoding transfer-encoding
        switch (strtolower($transferEncoding = @$headers['content-transfer-encoding'])) {
            case Zend_Mime::ENCODING_QUOTEDPRINTABLE:
                $content = quoted_printable_decode($encodedContent);
                break;
            case Zend_Mime::ENCODING_BASE64:
                $content = base64_decode($encodedContent);
                break;
            default:
                $content = $encodedContent;
        }

        $contentType = @$headers['content-type'] ? $headers['content-type']
            : AW_Helpdeskultimate_Helper_Config::DEFAULT_MIME_TYPE;

        foreach (explode(";", $contentType) as $headerPart) {
            $headerPart = strtolower(trim($headerPart));
            if (strpos($headerPart, 'charset=') !== false) {
                $charset = preg_replace('/charset=[^a-z0-9\-_]*([a-z\-_0-9]+)[^a-z0-9\-]*/i', "$1", $headerPart);
                return iconv($charset, AW_Helpdeskultimate_Helper_Config::STORAGE_ENCODING, $content);
            }
        }
        return $content;
    }

    /**
     * Returns stats for mailbox
     * @return
     */
    public function getStats()
    {
        $stats = new Varien_Object(
            array(
                'count_messages' => $this->getInstance()->countMessages()
            )
        );
        return $stats;
    }

    public function log()
    {
        $args = func_get_args();
        call_user_func_array(array(Mage::helper('helpdeskultimate/logger'), 'log'), array_values($args));
    }

    protected function _getGatewaySecure($gateway)
    {
        if ($gateway->getSecure() == AW_Helpdeskultimate_Model_Gateway::SECURE_NONE) {
            return null;
        }
        return $gateway->getSecure();

    }
}
