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

class AW_Helpdeskultimate_Model_Message extends AW_Core_Model_Abstract
{

    protected $_ticket;

    protected function _construct()
    {
        $this->_init('helpdeskultimate/message');
    }

    public function validate()
    {
        $errors = array();
        $helper = Mage::helper('helpdeskultimate');
        if (!Zend_Validate::is($this->getContent(), 'NotEmpty')) {
            $errors[] = $helper->__("Content can't be empty");
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    public function getTicket()
    {
        if ($this->_ticket) {
            return $this->_ticket;
        } elseif ($this->getTicketId()) {
            $this->_ticket = Mage::getModel('helpdeskultimate/ticket')->load($this->getTicketId());
            return $this->_ticket;
        } elseif ($this->getId()) {
            throw(
                new Mage_Core_Exception(
                    Mage::helper('helpdeskultimate')->__("Ticket not found for message #" . $this->getId())
                )
            );
        }
    }

    public function getFolderName()
    {
        $path = Mage::getBaseDir('media') . DS . 'helpdeskultimate' . DS . 'message-' . $this->getId() . DS;
        return $path;
    }

    public function getFilename()
    {
        // Override to clean up zombie files
        if (isset($this->_data['filename'])) {
            $fn = $this->_data['filename'];
        } else {
            return '';
        }
        $_existingFiles = array();
        foreach (explode('|', $fn) as $file) {
            if (Mage::helper('helpdeskultimate')->getRealFileName($this->getFolderName(), $file)) {
                $_existingFiles[] = $file;
            }
        }
        return $_existingFiles ? $_existingFiles : '';
    }

    protected function _beforeDelete()
    {
        // Delete file && folder
        $files = Mage::helper('helpdeskultimate')->getFiles($this->getFolderName());
        foreach ($files as $file) {
            @unlink($this->getFolderName() . $file);
        }
        @rmdir($this->getFolderName());
    }

    public function getFileUrl($file)
    {
        $realFileName = Mage::helper('helpdeskultimate')->getRealFileName($this->getFolderName(), $file);
        $params = array(
            'fid'      => base64_encode(Mage::helper('core')->encrypt($this->getId())),
            'file'     => base64_encode($realFileName),
            'basename' => base64_encode($file),
        );

        return Mage::getUrl(
            '*/*/downloadmsg',
            $params
        );
    }

    /**
     * Detects if message is department reply
     * @return bool
     */
    public function isDepartmentReply()
    {
        return !!$this->getData('department_id');
    }

    public function getAdminFileUrl($file)
    {
        $realFileName = Mage::helper('helpdeskultimate')->getRealFileName($this->getFolderName(), $file);
        $params = array(
            'id'       => $this->getId(),
            'file'     => base64_encode($realFileName),
            'basename' => base64_encode($file),
        );
        if (
            method_exists(
                Mage::getSingleton('adminhtml/url'),
                'getUrl'
            )
        ) {
            return Mage::getSingleton('adminhtml/url')->getUrl(
                '*/*/downloadmsg',
                $params
            );
        } else {
            return $this->getFileUrl($file);
        }
    }

    public function delete()
    {
        $this->_beforeDelete();
        parent::delete();
    }

    /**
     * Returns parsed content
     * @return string
     */
    public function getParsedContent()
    {
        $tf = Mage::getModel('core/email_template_filter');
        $tf
            ->setUseAbsoluteLinks(true)
            ->setVariables(array(
                'ticket' => $this->getTicket(),
                'department' => $this->getTicket()->getDepartment(),
                'message' => $this,
            ));
        try {
            return $tf->filter($this->getContent());
        } catch (Exception $e) {
            $this->log("Error occured while parsing text: {$e->getMessage()}");
        }
    }

    /**
     * Run before save
     * @return
     */
    public function _beforeSave()
    {
        if (!$this->getCreatedTime()) {
            $this->setCreatedTime(now());
        }
        return parent::_beforeSave();
    }
}
