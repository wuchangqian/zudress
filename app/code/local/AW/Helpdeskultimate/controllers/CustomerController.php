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

class AW_Helpdeskultimate_CustomerController extends Mage_Core_Controller_Front_Action
{

    const SIZE_MEGABYTE = 1048576;
    const SIZE_KILOBYTE = 1024;

    public function revalidateAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

    public function indexAction()
    {
        if (!$this->_getCustomerSession()->getCustomerId()) {
            $this->_getCustomerSession()->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('helpdeskultimate/customer');
        }
        if ($block = $this->getLayout()->getBlock('helpdeskultimate_customer_tickets')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        if ($block = $this->getLayout()->getBlock('helpdeskultimate_ticketform')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->getLayout()->getBlock('head')->setTitle($this->__('My Support Tickets'));
        $this->renderLayout();
    }

    public function viewextAction()
    {
        if (!$this->_helper('config')->isAllowedExternalView()) {
            return $this->_redirect('helpdeskultimate/customer');
        }
        $uid = $this->getRequest()->getParam('uid');
        $key = $this->getRequest()->getParam('key');

        if ($ticket = Mage::getModel('helpdeskultimate/ticket')->loadExternal(urldecode($uid), $key)) {
            if (is_null($ticket->getId())) {
                $this->_getSession()->addError($this->__('Ticket not found'));
                $this->_redirect('customer/account/login/');
            }
            $customerIdFromSession = $this->_getCustomerSession()->getCustomerId();
            if ($ticket->getCustomerId() && ($ticket->getCustomerId() == $customerIdFromSession)) {
                return $this->_redirectUrl($ticket->getCustomerUrl());
            }
            // Ticket access granted
            $this->loadLayout();
            $this->getLayout()->getBlock('external_ticket')->setTicket($ticket);
            $this->getLayout()->getBlock('external_messages')->setTicket($ticket);
            $this->getLayout()->getBlock('external_replyfrom')->setTicket($ticket);
            $this->getLayout()->getBlock('head')->setTitle($this->__('Ticket details'));
            $this->renderLayout();
            return;
        } else {
            $this->_redirect('/');
        }
    }

    public function viewAction()
    {
        if (!$this->_getCustomerSession()->getCustomerId()) {
            $this->_getCustomerSession()->authenticate($this);
            return;
        }
        $this->loadLayout();
        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('helpdeskultimate/customer');
        }

        $ticketId = $this->getRequest()->getParam('id');
        if ($ticketId) {
            $ticket = Mage::getModel('helpdeskultimate/ticket')->load($ticketId);
            if ($ticket->getCustomerId() == $this->_getCustomerSession()->getCustomer()->getId()) {
                $this->getLayout()->getBlock('head')->setTitle($this->__('Ticket details'));
                $this->renderLayout();
                return;
            }
        }
        $this->_redirect(('helpdeskultimate/customer'));
    }

    /* Creates new ticket */
    public function newAction()
    {
        if (!$this->_getCustomerSession()->getCustomerId()) {
            $this->_getCustomerSession()->authenticate($this);
            return;
        }
        $session = Mage::getSingleton('core/session');
        $customer = $this->_getCustomerSession()->getCustomer();

        $proto = Mage::getModel('helpdeskultimate/proto');
        $postData = $this->getRequest()->getPost();

        if (isset($postData['department_id'])) {
            $proto->setDepartmentId($postData['department_id']);
        }
        try {

            $proto
                ->setSubject(@$postData['title'])
                ->setContent(@$postData['content'])
                ->setPriority(@$postData['priority'])
                ->setStoreId(Mage::app()->getStore()->getId())
                ->setFrom(Mage::getSingleton('customer/customer')->getId())
                ->setSource('web');

            $isAllowedOrdersLinking = $this->_helper('config')->isAllowedOrdersLinking();
            if (isset($postData['order_id']) && intval($postData['order_id']) > 0 && $isAllowedOrdersLinking) {
                $order = Mage::getModel('sales/order')->load(intval($postData['order_id']));
                if (!is_null($order->getId()) && $order->getCustomerId() == $customer->getId()) {
                    $proto->setOrderId($order->getId());
                } else {
                    unset($postData['order_id']);
                    Mage::throwException($this->__('Invalid order number'));
                }
            }

            $validateResult = $proto->validate();
            $this->_validateProcess($validateResult, $this->__('Unable to post ticket. Please, try again later.'));
            $uploader = $this->_createUploader();
            if (!is_null($uploader)) {
                $proto->setFilename($_FILES['filename']['name']);
            }
            $proto
                ->setFrom($customer->getId())
                ->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PENDING)
                ->save();

            if (!is_null($uploader)) {
                $path = $proto->getFolderName();
                @mkdir($path);
                $_filename = Mage::helper('helpdeskultimate')->getEncodedFileName($proto->getData('filename'));
                $uploader->save($path, $_filename);
            }

            $session->addSuccess($this->__('Your ticket has been submitted. Thank you.'));
            $proto->convertToTicket();
            $proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PROCESSED)->save();

        } catch (Exception $e) {
            // Something goes wrong. Show what
            $proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_FAILED)->save();
            Mage::getSingleton('helpdeskultimate/session')->setFormData($postData);
            $session->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }


    public function postReplyAction()
    {
        $uid = $this->getRequest()->getParam('uid');
        $key = $this->getRequest()->getParam('key');
        if (
            $uid
            && $key
            && ($ticket = Mage::getModel('helpdeskultimate/ticket')->loadExternal(urldecode($uid), $key))
        ) {
            if (!$this->_helper('config')->isAllowedExternalView()) {
                $this->_redirect('helpdeskultimate/customer');
            }
            $customer = new Varien_Object(array('name' => $ticket->getCustomerName()));
        } else {
            if (!$this->_getCustomerSession()->getCustomerId()) {
                $this->_getCustomerSession()->authenticate($this);
                return;
            }
            $threadId = $this->getRequest()->getParam('id');
            $ticket = Mage::getModel('helpdeskultimate/ticket')->load($threadId);
            $customer = $this->_getCustomerSession()->getCustomer();
        }
        if (!$ticket->getData() ||
            (
                $ticket->getData('customer_id') &&
                    $customer->hasData('email') &&
                    $customer->getEmail() != $ticket->getData('customer_email')
            )
        ) {
            return $this->_redirect('helpdeskultimate/customer');
        }
        $message = Mage::getModel('helpdeskultimate/message');
        $session = Mage::getSingleton('core/session');

        $data = $this->getRequest()->getPost();

        // Set ticket status to open
        $ticket
            ->setIsChangedByCustomer(true)
            ->setStatus(AW_Helpdeskultimate_Model_Status::STATUS_OPEN);

        /* Insert */
        try {
            $message->setContent(trim($data['content']));
            $validateResult = $message->validate();

            $this->_validateProcess($validateResult, $this->__('Unable to post reply. Please, try again later.'));
            $uploader = $this->_createUploader();
            if (!is_null($uploader)) {
                $message->setFilename($_FILES['filename']['name']);
            }

            $_authName = isset($customer) ? $customer->getName() : $ticket->getCustomerName();
            $message
                ->setCreatedTime(date('Y-m-d H:i:s'))
                ->setTicketId($ticket->getId())
                ->setAuthorName($_authName)
                ->save();

            if (!is_null($uploader)) {
                $path = $message->getFolderName();
                $fileName = Mage::helper('helpdeskultimate')->getEncodedFileName($message->getData('filename'));
                @mkdir($path);
                $uploader->save($path, $fileName);
            }
            $ticket->save();
            $session->addSuccess($this->__('Your ticket reply has been submitted. Thank you.'));
            // Notify admin about reply in ticket
            Mage::helper("helpdeskultimate/notify")->ticketReplyToAdmin($message);

        } catch (Exception $e) {
            // Something goes wrong. Show what

            Mage::getSingleton('helpdeskultimate/session')->setFormData($data);
            $session->addError($e->getMessage());
            $this->_redirectReferer();
            return;
        }
        if (isset($customer)) {
            if ($ticket->getData('customer_id')) {
                $this->_redirectReferer();
                //$this->_redirect('*/*/'); //redirect to ticket page
            } else {
                $this->_redirect('*/*/viewext', array(
                    'uid' => $this->getRequest()->getParam('uid'),
                    'key' => $this->getRequest()->getParam('key')
                ));
            }
        } else {
            $this->_redirectReferer();
        }
    }

    public function ticketCloseAction()
    {
        $uid = $this->getRequest()->getParam('uid', false);
        $key = $this->getRequest()->getParam('key', false);
        $ticketId = $this->getRequest()->getParam('id', 0);
        if ($uid && $key) {
            $ticket = Mage::getModel('helpdeskultimate/ticket')->loadExternal(urldecode($uid), $key);
        }
        if (isset($ticket)) {
            if (!$this->_helper('config')->isAllowedExternalView()) {
                $this->_redirect('helpdeskultimate/customer');
            }
            $customer = new Varien_Object(array('name' => $ticket->getCustomerName()));
        } else {
            if (!$this->_getCustomerSession()->getCustomerId()) {
                $this->_getCustomerSession()->authenticate($this);
                return;
            }
            $ticket = Mage::getModel('helpdeskultimate/ticket')->load($ticketId);
            $customer = $this->_getCustomerSession()->getCustomer();
        }
        if (is_null($ticket->getId()) || (!is_null($ticket->getData('customer_id')) &&
            $customer->hasData('email') &&
            $customer->getData('email') != $ticket->getData('customer_email')
        )
        ) {
            return $this->_redirect('helpdeskultimate/customer');
        }

        try {
            $ticket
                ->setStatus(AW_Helpdeskultimate_Model_Status::STATUS_CLOSED)
                ->save();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_redirectReferer();
            return;
        }

        if (isset($customer)) {
            if ($ticket->getData('customer_id')) {
                $this->_redirectReferer();
            } else {
                $this->_redirect('*/*/viewext', array(
                    'uid' => $this->getRequest()->getParam('uid'),
                    'key' => $this->getRequest()->getParam('key')
                ));
            }
        } else {
            $this->_redirectReferer();
        }
    }

    public function downloadmsgAction()
    {
        // Downloads file from message
        $message = false;
        if ($fid = $this->getRequest()->getParam('fid')) {
            $fid = Mage::helper('core')->decrypt(base64_decode($fid));
            $message = Mage::getModel('helpdeskultimate/message')->load($fid);
        }
        if (!$message && $messageId = $this->getRequest()->getParam('id')) {
            $message = Mage::getModel('helpdeskultimate/message')->load($messageId);
        }
        if (!$message || !$message->getTicket()) {
            return $this->_redirect('*/*/');
        }
        $customerId = $message->getTicket()->getCustomerId();
        $folderName = $message->getFolderName();
        if (!$this->_sendFile($customerId, $folderName)) {
            return $this->_redirect('*/*/');
        }
    }

    public function downloadtckAction()
    {
        // Downloads file from ticket
        $ticket = false;
        if ($fid = $this->getRequest()->getParam('fid')) {
            $fid = Mage::helper('core')->decrypt(base64_decode($fid));
            $ticket = Mage::getModel('helpdeskultimate/ticket')->load($fid);
        }
        if (!$ticket && $ticketId = $this->getRequest()->getParam('id')) {
            $ticket = Mage::getModel('helpdeskultimate/ticket')->load($ticketId);
        }
        if (!$ticket) {
            return $this->_redirect('*/*/');
        }
        $customerId = $ticket->getCustomerId();
        $folderName = $ticket->getFolderName();
        if (!$this->_sendFile($customerId, $folderName)) {
            return $this->_redirect('*/*/');
        }
    }

    private function _helper($code = null)
    {
        $_helper = 'helpdeskultimate' . ((!is_null($code)) ? ('/' . $code) : '');
        return Mage::helper($_helper);
    }

    private function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    private function _validateProcess($validateResult = true, $defaultMsg = '')
    {
        if ($validateResult !== true) {
            $msg = array();
            if (is_array($validateResult)) {
                foreach ($validateResult as $errorMessage) {
                    $msg[] = ($errorMessage);
                }
            } else {
                $msg[] = $defaultMsg;
            }
            throw(new Exception(implode("\r\n", $msg)));
        }
        return;
    }

    private function _createUploader()
    {
        if (isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
            // Check if file exceeds size
            $maxUploadSize = $this->_helper('config')->getAttachmentsMaxUploadFileSize() * self::SIZE_MEGABYTE;
            if ($_FILES['filename']['size'] > $maxUploadSize || $_FILES['filename']['error'] === UPLOAD_ERR_INI_SIZE) {
                throw new Exception($this->__("Uploaded file is too large"));
            }
            /* Starting upload */
            $uploader = new Varien_File_Uploader('filename');
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);

            return $uploader;
        }
        return null;
    }

    private function _sendFile($customerId, $folderName, $basename = null)
    {
        if (!$customerId ||
            $customerId == $this->_getCustomerSession()->getCustomer()->getId()
        ) {
            if (!is_null($this->getRequest()->getParam('basename', null))) {
                $basename = base64_decode($this->getRequest()->getParam('basename'));
            }
            $file = base64_decode($this->getRequest()->getParam('file'));
            Mage::helper('helpdeskultimate')->sendFile($folderName . DS . $file, $basename);
            return true;
        }
        return false;
    }

    private function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}
