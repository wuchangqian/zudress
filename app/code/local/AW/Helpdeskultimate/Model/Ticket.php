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

class AW_Helpdeskultimate_Model_Ticket extends AW_Core_Model_Abstract
{

    const CREATED_BY_CUSTOMER = 'customer';
    const CREATED_BY_ADMIN = 'admin';

    const DB_DATETIME_FORMAT = 'yyyy-MM-dd H:m:s'; // DON'T use Y(uppercase) here

    const XML_ORDERS_ENABLED = 'helpdeskultimate/advanced/orders_enabled';

    const ABUSE_IDS = 'sex,wtf,fuc,fuk,fck,ass,hui,dck,pzd,ebl,bla,xep';

    protected $_department;
    protected $_customer;
    protected $_order = null;

    protected function _construct()
    {
        $this->_init('helpdeskultimate/ticket');
    }


    /**
     * Generates UID for ticket
     * @return string
     */
    public function generateUid()
    {
        do {
            $digits = (string)rand(100000, 199999);
            $digits = substr($digits, 1);

            $letters = '';
            $aZ = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $aZCount = strlen($aZ);
            for ($i=0; $i<3; $i++) {
                $int = rand(0, $aZCount-1);
                $letters .= $aZ[$int];
            }

            $sameTicket = Mage::getModel('helpdeskultimate/ticket')->loadByUid($letters.'-'.$digits);
        } while ($sameTicket->getData() || stripos(self::ABUSE_IDS, $letters) !== false);

        return $letters.'-'.$digits;
    }


    public function getStatusText()
    {
        $states = AW_Helpdeskultimate_Model_Status::getOptionArray();
        return @$states[$this->getStatus()];
    }

    /**
     * Returns messages count for ticket
     * @return int
     */
    public function getMessagesCount()
    {
        return $this->getMessages()->count();
    }

    /**
     * Returns true if ticket is locked
     * @return bool
     */
    public function isReadOnly()
    {
        //is not admin user
        if (!is_object(Mage::getSingleton('admin/session')->getUser())) {
            return false;
        }
        if ($this->getOrigData('locked_by') == Mage::getSingleton('admin/session')->getUser()->getId())
            return false;
        return !!$this->getOrigData('locked_by');
    }

    /**
     * Returns user locked the ticket
     * @return Mage_Admin_Model_User
     */
    public function getLockedUser()
    {
        return Mage::getSingleton('admin/user')->load($this->getLockedBy());
    }

    /**
     * Run before id
     * @return
     */
    public function _beforeSave()
    {
        if ($this->isReadOnly()) {
            Mage::throwException('Ticket is locked by another customer');
        }

        if (!$this->getUid()) {
            $this->setUid($this->generateUid());
        }
        $this->setIsVirtual(intval(!$this->getCustomerId()));
        if (!$this->getCreatedTime()) {
            $this->setCreatedTime(now());
        }
        if (!$this->getCreatedBy()) {
            $this->setCreatedBy('customer');
        }
        if (!$this->getContentType()) {
            $this->setContentType(AW_Helpdeskultimate_Helper_Config::DEFAULT_MIME_TYPE);
        }
        return parent::_beforeSave();
    }


    public function  _afterLoad()
    {
        if ($this->getDepartmentId()) {
            $this->setDepartmentSavedId($this->getDepartmentId());
        }
        if ($this->getStatus()) {
            $this->setStatusId($this->getStatus());
        }
        if ($this->getPriority()) {
            $this->setSavedPriority($this->getPriority());
        }
        return parent::_afterLoad();
    }

    /**
     * Save also information to flat table
     * @return AW_Helpdeskultimate_Model_Ticket
     */
    public function _afterSave()
    {
        $flat = Mage::getModel('helpdeskultimate/ticket_flat')
            ->load($this->getId(), 'ticket_id')
            ->setTicket($this)
            ->save();
        if (
            !$this->getSkipStatusHistory() &&

            @$this->_origData['status'] &&
             ($this->_data['status'] != $this->_origData['status'])
        ) {
            // Status changed. Save to history.
            Mage::getModel('awcore/logger')
                ->setData(array(
                    'title' => "Ticket #{$this->getUid()} status changed to '{$this->getStatusText()}'"
                             . " by '{$this->getDepartment()->getName()}'",
                    'module' => 'Helpdeskultimate',
                    'object' => 'AW_Helpdeskultimate_Model_Ticket',
                    'code' => 'AW_HDU_STATUS_CHANGE',
                    'custom_field_1' => $this->getId(),
                    'custom_field_2' => $this->getStatus(),
                    'custom_field_3' => $this->getDepartment()->getId(),
                    'custom_field_4' => (int)$this->getIsChangedByCustomer()
                ))
                ->save();
            $this->_origData['status'] = $this->_data['status'];
        }
        return parent::_afterSave();
    }

    /**
     * Tries to load ticket by UID
     * @param object $id
     * @return AW_Helpdeskultimate_Model_Ticket
     */
    public function loadByUid($id)
    {
        return parent::load($id, 'uid');
    }

    /**
     * Returns attach filename. Cleans automaticaly file from ticket if not exists
     * @return string
     */
    public function getFilename()
    {
        if (!isset($this->_data['filename'])) return '';
        $fn = $this->_data['filename'];
        $_existingFiles = array();
        foreach (explode('|', $fn) as $file) {
            if (Mage::helper('helpdeskultimate')->getRealFileName($this->getFolderName(), $file))
                $_existingFiles[] = $file;
        }
        return $_existingFiles ? $_existingFiles : '';
    }

    /**
     * Returns file url
     * @return
     */
    public function getFileUrl($file)
    {
        if (Mage::app()->getStore()->getCode() == 'admin') {
            return $this->getAdminFileUrl($file);
        }
        $realFile = Mage::helper('helpdeskultimate')->getRealFileName($this->getFolderName(), $file);
        $params = array(
            'fid'      => base64_encode(Mage::helper('core')->encrypt($this->getId())),
            'file'     => base64_encode($realFile),
            'basename' => base64_encode($file),
        );
        return Mage::getUrl(
            '*/*/downloadtck',
            $params
        );
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
                '*/*/downloadtck',
                $params
            );
        } else {
            return $this->getFileUrl($file);
        }
    }

    /**
     * Returns url to ticket in admin
     * @return string
     */
    public function getAdminUrl()
    {
        return Mage::getSingleton('adminhtml/url')
            ->getUrl('helpdeskultimate_admin/ticket/edit', array('id' => $this->getId()));
    }

    /**
     * Returns url to ticket in customer area
     * @return string
     */
    public function getCustomerUrl()
    {
        return Mage::getModel('core/url')->setStore($this->getStoreId())
            ->getUrl('helpdeskultimate/customer/view', array('id' => $this->getId()));
    }

    /**
     * Auto-detect current area and return url
     * @return string
     */
    public function getUrl()
    {
        if (Mage::app()->getStore()->getCode() == 'admin') {
            return $this->getAdminUrl();
        }
        return $this->getCustomerUrl();
    }


    public function getMessages()
    {
        return Mage::getModel('helpdeskultimate/message')
            ->getCollection()
            ->addTicketFilter($this->getId());
    }

    public function setCustomer($customer)
    {

        preg_match('/[^<]*/', $customer->getName(), $matches);
        $customerName = trim(reset($matches));

        $this
                ->setCustomerName($customerName)
                ->setCustomerEmail($customer->getEmail())
                ->setCustomerId(intval($customer->getId()));
        $this->_customer = $customer;

        return $this;
    }

    public function setCustomerId($id)
    {
        $this->setData('customer_id', $id);
        $this->_customer = null;
        return $this;
    }

    public function getCustomer()
    {
        if (!$this->_customer) {
            if ($this->getCustomerId()) {
                $this->_customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
            } else {
                $this->_customer = new Varien_Object;
                $this->_customer
                        ->setName($this->getData('customer_name'))
                        ->setEmail($this->getData('customer_email'));
            }
        }
        return $this->_customer;
    }

    public function getCustomerName()
    {
        return $this->getCustomer()->getName();
    }

    public function getCustomerEmail()
    {
        return $this->getCustomer()->getEmail();
    }


    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
        }
        return $this->_order;
    }

    /**
     * Returns department. If called once, returns from cache.
     * @return AW_Helpdeskultimate_Model_Department
     */
    public function getOldDepartment()
    {
        if (!$this->getData('old_department')) {
            $this->setData(
                'old_department',
                Mage::getModel('helpdeskultimate/department')->load($this->getOrigData('department_id'))
            );
        }
        return $this->getData('old_department');
    }

    /**
     * Returns department. If called once, returns from cache.
     * @return AW_Helpdeskultimate_Model_Department
     */
    public function getDepartment()
    {
        if (!$this->getData('department')) {
            $this->setData('department', Mage::getModel('helpdeskultimate/department')->load($this->getDepartmentId()));
        }
        return $this->getData('department');
    }

    public function getFolderName()
    {
        $path = Mage::getBaseDir('media') . DS . 'helpdeskultimate' . DS . 'ticket-' . $this->getId() . DS;
        if (!file_exists($path)) {
            @mkdir($path);
        }
        return $path;
    }

    /* Delete ticket and all associated files and all associated messages*/

    protected function _beforeDelete()
    {
        if ($this->isReadOnly()) {
            Mage::throwException('Ticket is locked by another customer');
        }

        $colls = Mage::getModel("helpdeskultimate/message")->getCollection()
            ->addTicketFilter($this->getId());

        foreach ($colls as $coll) {
            $coll->delete();
        }
        // Delete file && folder
        $files = Mage::helper('helpdeskultimate')->getFiles($this->getFolderName());
        foreach ($files as $file) {
            @unlink($this->getFolderName() . $file);
        }
        @rmdir($this->getFolderName());
    }


    public function delete()
    {
        $this->_beforeDelete();

        parent::delete();
    }


    public function validate()
    {

        $errors = array();
        $helper = Mage::helper('helpdeskultimate');

        if (!Zend_Validate::is($this->getTitle(), 'NotEmpty')) {
            $errors[] = $helper->__('Please specify title');
        }

        if (!Zend_Validate::is($this->getContent(), 'NotEmpty')) {
            $errors[] = $helper->__("Content can't be empty");
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    public function getAccessKey()
    {
        return Mage::helper('core')->encrypt($this->getUid());
    }

    public function getAccessUrl($forceExternalUrl = null)
    {
        if ($forceExternalUrl === null) {
            $forceExternalUrl = Mage::getStoreConfig(
                AW_Helpdeskultimate_Helper_Config::XML_PATH_ALLOWEXTERNAL,
                $this->getStoreId()
            );
        }
        if ($this->getCustomerId() && !$forceExternalUrl) {
            return $this->getCustomerUrl();
        }
        return Mage::getModel('core/url')
            ->setStore($this->getStoreId())
            ->getUrl(
                'helpdeskultimate/customer/viewext/',
                array(
                    'uid' => base64_encode($this->getAccessKey()),
                    'key' => Mage::helper('core')->getHash($this->getUid()),
                )
            );
    }

    public function getCustomerUrlHtml()
    {
        return Mage::helper('helpdeskultimate')->__(
            'You can view the ticket and reply from web interface from %s here %s.',
            '<a href="' . $this->getAccessUrl() . '">',
            '</a>'
        );
    }


    public function loadExternal($uid, $key)
    {
        $uid = Mage::helper('core')->decrypt(base64_decode($uid));
        if (Mage::helper('core')->getHash($uid) == $key) {
            return $this->loadByUid($uid);
        }
        return false;
    }


    /**
     * Sets department id and reloads department
     * @param object $id
     * @return AW_Helpdeskultimate_Model_Ticket
     */
    public function setDepartmentId($id, $force = null)
    {
        if (!$force) {
            $this->setDepartment(Mage::getModel('helpdeskultimate/department')->load(intval($id)));
        }
        $this->setData('department_id', $this->getDepartment()->getId());
        return $this;
    }

    /**
     * Sets department and departments id to specified entity
     * @param AW_Helpdeskultimate_Model_Department $department
     * @return AW_Helpdeskultimate_Model_Ticket
     */
    public function setDepartment(AW_Helpdeskultimate_Model_Department $department)
    {
        $this
                ->setData('department', $department)
                ->setData('department_id', $department->getId());
        return $this;
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
        //        ->setUseSessionInUrl(false)
                ->setVariables(array('ticket' => $this, 'department' => $this->getDepartment()));
        try {
            return $tf->filter($this->getContent());
        }
        catch (Exception $e) {
            $this->log("Error occured while parsing text: {$e->getMessage()}");
        }
    }

    public function getInitiator()
    {
        $entity = new Varien_Object;
        if ($this->getCreatedBy() == 'admin') {
            // Admin initiations
            $entity = $this->getDepartment();
        } else {
            $entity = $this->getCustomer();
        }
        return $entity;
    }
}
