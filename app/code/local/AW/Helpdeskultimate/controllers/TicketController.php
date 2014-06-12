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


class AW_Helpdeskultimate_TicketController extends Mage_Adminhtml_Controller_Action
{

    const SIZE_MEGABYTE = 1048576;

    protected function _isAllowed()
    {
        $allowedCondition = Mage::getSingleton('admin/session')->isAllowed('helpdeskultimate/index');
        if (in_array($this->getRequest()->getActionName(), array('edit', 'delete', 'removemessage', 'savemessage', 'save'))) {
            $role = Mage::getSingleton('admin/session')->getUser()->getRole();
            $departmentPermissions = Mage::getModel('helpdeskultimate/department_permissions')
                ->loadByRoleId($role->getId());
            $id = $this->getRequest()->getParam('id', 0);
            $ticket = Mage::getModel('helpdeskultimate/ticket')->load($id);
            if (!is_null($departmentPermissions->getId()) && !is_null($ticket->getId())) {
                $allowedCondition = in_array($ticket->getData('department_id'), $departmentPermissions->getValue());
            }
        }
        return $allowedCondition;
    }

    public function editAction()
    {
        /* Inits edit ticket form */
        $id = $this->getRequest()->getParam('id');

        $ticket = Mage::getModel('helpdeskultimate/ticket');

        $ticket->load($id);
        if ($id !== 0 && !$ticket->getData()) {
            $this->_getSession()->addError($this->__('Couldn\'t load ticket by given ID'));
            return $this->_redirect('*/index/index');
        }

        if ($ticket->isReadOnly()) {
            $format = Mage::app()->getLocale()->getDateTimeFormat(
                Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
            );
            $datetime = Mage::app()->getLocale()->date($ticket->getLockedAt(), Varien_Date::DATETIME_INTERNAL_FORMAT)
                ->toString($format);
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__("Ticket has been locked by %s at %s", $ticket->getLockedUser()->getName(), $datetime)
            );
        }

        if ($this->getRequest()->getParam('customer_id')) {
            if ($customer = Mage::getModel('customer/customer')->load($this->getRequest()->getParam('customer_id'))) {
                $ticket->setCustomer($customer);
            }
        }

        if ($this->getRequest()->getParam('order_id') && !$id) {
            if ($order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'))) {
                $customer = new Varien_Object;
                $customer
                        ->setEmail($order->getCustomerEmail())
                        ->setName($order->getCustomerFirstname() . " " . $order->getCustomerLastname())
                        ->setId($order->getCustomerId());
                $ticket
                        ->setTitle(Mage::helper('helpdeskultimate')->__("Order ID %s", $order->getIncrementId()))
                        ->setCustomer($customer)
                        ->setStoreId($order->getStore()->getId())
                        ->setOrderIncrementalId($order->getIncrementId());
            }
        }

        Mage::register('hdu_ticket_data', $ticket);

        $this->_title($this->__('Help Desk - Tickets'));
        $_title = is_null($ticket->getId()) ? $this->__('Create New Ticket') : '#' . $ticket->getUid();
        $this->_title($_title);
        $this->loadLayout()->_setActiveMenu('helpdeskultimate');

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent(
            $this->getLayout()
                ->createBlock('core/template')
                ->setTemplate('helpdeskultimate/head.phtml')
        );

        $this->_addContent($this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_edit'))
                ->_addLeft($this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_edit_tabs'));
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        $this->renderLayout();

        Mage::getSingleton('adminhtml/session')->setFormData(null);
    }

    public function newAction()
    {
        $this->_forward('edit', null, null, array('id' => 0));
    }

    public function saveTicketAction()
    {
        // Saves new ticket
        try {
            if ($data = $this->getRequest()->getPost()) {

                $customer = Mage::getModel('customer/customer');

                $ticket = Mage::getModel('helpdeskultimate/ticket');
                if (isset($data['customer_id']) && $data['customer_id']) {
                    if (is_numeric($data['customer_id']) && $data['customer_id'] > 0) {
                        // Registered customer
                        $customer->load($data['customer_id']);
                    } elseif (is_string($data['customer_id'])) {
                        if ($email = Mage::helper('helpdeskultimate/imap')->parseEmailAddress($data['customer_id'])) {
                            $name = trim(preg_replace("#[^\s]*@[^\s]*#", "", $data['customer_id']));
                            if (!$name)
                                $name = $email;
                            $customer
                                    ->setEmail($email)
                                    ->setFirstname($name);
                        }
                    }
                }
                if (!$customer->getEmail()) {
                    throw new Mage_Core_Exception("Please enter at least customer email");
                }
                $department = Mage::helper('helpdeskultimate')->getDepartment($data['department_saved_id']);

                $uploaders = $this->_createUploader();
                $this->_addFileNames($uploaders, $ticket);

                if ($data['lock']) {
                    $ticket
                            ->setLockedBy(Mage::getSingleton('admin/session')->getUser()->getId())
                            ->setLockedAt(now());
                } else {
                    $ticket
                            ->setLockedBy(0);
                }

                $ticket
                        ->setPriority(@$data['saved_priority'])
                        ->setCustomer($customer);

                /* Assign order inc id */
                $ticket->setOrderIncrementalId(preg_replace(
                    "/[^0-9a-z\-]+/sui",
                    "",
                    urldecode(@$data['order_incremental_id'])
                ));
                $orderId = Mage::getModel('sales/order')->loadByIncrementId($ticket->getOrderIncrementalId())->getId();
                // Try to assign order
                if ($orderId) {
                    $ticket->setOrderId($orderId);
                } else {
                    $ticket->setOrderId(0);
                }

                /* Set store id */
                if (isset($data['store_id'])) {
                    $ticket->setStoreId($data['store_id']);
                } else {
                    $storeIds = Mage::getModel('core/store')->getCollection()->getAllIds();
                    $ticket->setStoreId($storeIds[1]);
                }

                $ticket
                        ->setStatus($data['status_id'])
                        ->setTitle($data['title'])
                        ->setContent($data['content'])
                        ->setCreatedBy('admin')
                        ->setCreatedTime(now())
                        ->setDepartmentId($department->getId())
                        ->setIsVirtual(intval($customer->getId()))
                        ->setNote($data['note'])
                        ->save();

                if (!is_null($uploaders)) {
                    $path = $ticket->getFolderName();
                    foreach ($uploaders as $uploader) {
                        $fileName = Mage::helper('helpdeskultimate')->getEncodedFileName($uploader['filename']);
                        $uploader['uploader']->save($path, $fileName);
                    }
                }

                if ($this->getRequest()->getParam('add_comment_to_order', false)) {
                    $this->_addCommentToOrder($ticket->getOrderId(), $data['content']);
                }

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ticket created'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                /* Notify customer by email if button is set */
                if (($ticket->getCustomerEmail()) && (isset($data['email']) && $data['email'])) {
                    Mage::helper('helpdeskultimate/notify')->ticketNew($ticket, $customer);
                }
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $ticket->getId()));
                    return;
                }

                $this->_redirect('*/');
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirectReferer();
            return;
        }
    }

    public function saveAction()
    {
        try {

            if ($data = $this->getRequest()->getPost()) {
                $id = $this->getRequest()->getParam('id');
                if (!$id) {
                    return $this->saveTicketAction();
                }
                // Create message from input stream
                $message = Mage::getModel('helpdeskultimate/message');
                $ticket = Mage::getModel('helpdeskultimate/ticket')->load($id);

                if (!$ticket) {
                    throw(new Exception($this->__('Ticket not %d found', $id)));
                }

                if ($ticket->isReadOnly()) {
                    throw(new Exception($this->__(
                        "Ticket has been locked by %s at %s",
                        $ticket->getLockedUser()->getName(),
                        $ticket->getLockedAt()
                    )));
                }
                if (isset($data['store_id'])) {
                    $ticket->setStoreId($data['store_id']);
                }
                /* Note */
                $ticket->setStatus($data['status_id'])->setPriority(@$data['saved_priority'])->setNote(@$data['note']);

                /* Lock */
                if ($data['lock']) {
                    $ticket->setLockedBy(Mage::getSingleton('admin/session')->getUser()->getId())->setLockedAt(now());
                } else {
                    $ticket->setLockedBy(0);
                }
 
                /* Assign order inc id */
                $ticket->setOrderIncrementalId(preg_replace(
                    "/[^0-9a-z\-]+/sui",
                    "",
                    urldecode(@$data['order_incremental_id'])
                ));
                if ($ticket->getOrderIncrementalId()) {
                    // Try to assign order
                    if ($orderId = Mage::getModel('sales/order')->loadByIncrementId(
                        $ticket->getOrderIncrementalId())->getId()
                    ) {
                        $ticket->setOrderId($orderId);
                    }
                }
                if (!$ticket->getOrderIncrementalId() || !isset($orderId)) {
                    $ticket->setOrderId(0);
                }
              
                /* */
                $this->_prepareCustomer($ticket);
                /* */
                $department = $this->_processReassignedDepartment($ticket);                
 
                $sendReplyToCustomer = false;
                if (trim($data['content']) || $this->_getMessagesInPost()) {
                    /* Save attachment */

                    $uploaders = $this->_createUploader();
                    $this->_addFileNames($uploaders, $message);

                    $message->setTicketId($id)
                            ->setContent($data['content'])
                            ->setCreatedTime(date('Y-m-d H:i:s'))
                            ->setDepartmentId($department->getId())
                            ->setAuthorName($ticket->getOldDepartment()->getName())
                            ->save();

                    if (!is_null($uploaders)) {
                        $path = $message->getFolderName();
                        foreach ($uploaders as $uploader) {
                            $fileName = Mage::helper('helpdeskultimate')->getEncodedFileName($uploader['filename']);
                            $uploader['uploader']->save($path, $fileName);
                        }
                    }

                    if (trim($data['content']) && $this->getRequest()->getParam('add_comment_to_order', false)) {
                        $this->_addCommentToOrder($ticket->getOrderId(), $data['content']);
                    }

                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ticket reply saved'));
                    if (($ticket->getCustomerEmail()) && (isset($data['email']) && $data['email'])) {
                        $sendReplyToCustomer = true;
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Ticket options saved'));
                }

                $ticket->save();

                /* send email after save process for request updates to take effect */
                if ($sendReplyToCustomer === true) {
                    Mage::helper('helpdeskultimate/notify')->ticketReplyToCustomer($message);
                }

                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $id));
                    return;
                }
                $this->_redirect('*/');
                return;
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);

            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
    }

    public function downloadmsgAction()
    {
        $messageId = $this->getRequest()->getParam('id');
        $message = Mage::getModel('helpdeskultimate/message')->load($messageId);
        if ($message->getFilename()) {
            $this->_sendFile($message->getFolderName());
        }
    }

    public function downloadtckAction()
    {
        $ticketId = $this->getRequest()->getParam('id');
        $ticket = Mage::getModel('helpdeskultimate/ticket')->load($ticketId);
        if ($ticket->getFilename()) {
            $this->_sendFile($ticket->getFolderName());
        }
    }

    public function usersuggestAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        /* Returns options for HTML select with autosuggested user names */
        $letters = @mysql_escape_string(urldecode($this->getRequest()->getParam('letters')));

        // Now we should filter customers

        $users = Mage::getModel('helpdeskultimate/source_users')->addNameFilter(($letters));
        $users->getCollection()->getSelect()->limit(50);

        $match100 = false;

        $unregistered = Mage::getModel('sales/quote_address')->getCollection();
        $unregistered
                ->getSelect()
                ->where(
                    "email LIKE '%$letters%' OR
                    CONCAT(firstname, lastname) LIKE '%letters%' OR
                    firstname LIKE '%$letters%' OR
                    lastname LIKE '%$letters%'"
                )
                ->group('email')
                ->limit(50);


        $options = array();
        foreach ($unregistered as $unregUser) {
            if (!$unregUser->getEmail()) {
                continue;
            }
            $flag = true;
            foreach ($users->getCollection() as $regUser) {
                if ($regUser->getEmail() == $unregUser->getEmail()) {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                if ($unregUser->getEmail() == $letters) {
                    $match100 = 1;
                }
                $options[] = array(
                    'label' => "{$unregUser->getFirstname()} {$unregUser->getLastname()} <{$unregUser->getEmail()}> [Unregistered]",
                    'value' => "{$unregUser->getFirstname()} {$unregUser->getLastname()} {$unregUser->getEmail()}",
                    'is_belong_to_order' => Mage::helper('helpdeskultimate')
                        ->isOrderBelongToCustomer($orderId, null, $unregUser->getEmail())
                );
            }
        }

        foreach ($users->getCollection() as $user) {
            if ($user->getEmail() == $letters) {
                $match100 = 1;
            }
            $options[] = array(
                'label' => "{$user->getFirstname()} {$user->getLastname()} <{$user->getEmail()}>",
                'value' => $user->getId(),
                'is_belong_to_order' => Mage::helper('helpdeskultimate')
                    ->isOrderBelongToCustomer($orderId, $user->getId(), $user->getEmail())
            );
        }

        if (!$match100) {
            $email = Mage::helper('helpdeskultimate/imap')->parseEmailAddress($letters);
            if ($email) {
                $options[] = array(
                    'label' => $letters . " <$letters>[Unregistered]",
                    'value' => $letters . " " . $letters,
                    'is_belong_to_order' => Mage::helper('helpdeskultimate')
                        ->isOrderBelongToCustomer($orderId, null, $email)
                );
            }
        }

        $this->getResponse()->setBody(Zend_Json::encode($options));
        return;
    }

    public function ajaxfindorderAction()
    {
        $customerId = $this->getRequest()->getParam('customer_id');
        $customerEmail = $this->getRequest()->getParam('customer_email');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId != '') {
            $orderList = Mage::getModel('sales/order')->getCollection();
            $orderList->getSelect()->where("increment_id LIKE ?", '%' . $orderId . '%');
            $count = $orderList->getSize();
            $orderList->getSelect()->limit(5, 0);
            $likeOrder = array();
            $i = 0;
            foreach ($orderList as $order) {
                $likeOrder[$i]['value'] = $order->getId();
                $likeOrder[$i]['label'] = $order->getIncrementId();
                $likeOrder[$i]['is_belong_to_customer'] = Mage::helper('helpdeskultimate')
                    ->isOrderBelongToCustomer($order->getId(), $customerId, $customerEmail);
                $i++;
            }
            $html = $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_edit_tab_operations_suggest')
                    ->setOptions($likeOrder)
                    ->setCount($count)
                    ->getHtml();
            $this->getResponse()->setBody($html);
            return;
        }
    }

    public function ajaxgettplcontentAction()
    {
        $template = Mage::getModel('helpdeskultimate/template')->load($this->getRequest()->getParam('id'));
        $ticket   = Mage::getModel('helpdeskultimate/ticket')->load($this->getRequest()->getParam('ticket_id'));
        if ($template->getId()) {
            $content = $template->getContent();
            if ($ticket->getId()) {
                $filter = Mage::getModel('core/email_template_filter');
                $filter->setStoreId($ticket->getStoreId());
                $filter->setVariables(array(
                    'customer' => $ticket->getCustomer()
                ));
                $content = $filter->filter($content);
            }
            $this->getResponse()->setBody($content);
        }
        return;
    }

    public function historyGridAction()
    {
        if ($ticketId = $this->getRequest()->getParam('id')) {
            $ticket = Mage::getModel('helpdeskultimate/ticket')->load($ticketId);
            $this->loadLayout();
            $this->getResponse()->setBody(
                $this->getLayout()
                    ->createBlock('helpdeskultimate/adminhtml_tickets_edit_tab_history_grid')
                    ->setTicket($ticket)
                    ->setCustomerEmailFilter($ticket->getCustomerEmail())
                    ->toHtml()
            );
        }
        return;
    }

    protected function deleteAction()
    {

        if (($ticketId = $this->getRequest()->getParam('id'))) {
            $ticket = Mage::getModel('helpdeskultimate/ticket')->load($ticketId);
            if ($ticket->isReadOnly()) {
                $this->_getSession()->addError($this->__('Incorrect operation'));
                return $this->_redirectReferer();
            }
            if ($ticket->getData()) {
                $ticket->delete();
                $this->_getSession()->addSuccess($this->__('Ticket has been successfully deleted'));
            }
        }
        return $this->_redirect('helpdeskultimate_admin/index/index');
    }

    public function removemessageAction()
    {
        $message = Mage::getModel('helpdeskultimate/message')->load($this->getRequest()->getParam('mid'));
        if ($message->getData() && $message->isDepartmentReply() && ($ticketId = $message->getData('ticket_id'))) {
            $ticket = $message->getTicket();
            if ($ticket->isReadOnly()) {
                $this->_getSession()->addError($this->__('Incorrect operation'));
                return $this->_redirectReferer();
            }
            $message->delete();
            $ticketFlat = Mage::getModel('helpdeskultimate/ticket_flat')->loadByTicketId($ticketId);
            $ticketFlat->setData('total_replies', $ticket->getMessagesCount())
                    ->save();
            $this->_getSession()->addSuccess($this->__('Message has been successfully removed'));
            return $this->_redirect('helpdeskultimate_admin/ticket/edit', array('id' => $ticketId));
        }
        return $this->_redirect('helpdeskultimate_admin/ticket/edit', array(
            'id' => $this->getRequest()->getParam('id')
        ));
    }

    public function savemessagebodyAction()
    {
        $result = array('s' => false);
        $message = Mage::getModel('helpdeskultimate/message')->load($this->getRequest()->getParam('id'));
        $ticket = $message->getTicket();
        if ($ticket->isReadOnly()) {
            $result['text'] = $this->__(
                'Ticket has been locked by %s at %s',
                $ticket->getLockedUser()->getName(),
                $ticket->getLockedAt()
            );
            echo Zend_Json::encode($result);
            return $this;
        }
        if ($message->getData()) {
            $text = $this->getRequest()->getParam('text');
            if (strpos($text, "\r\n") === false)
                $text = str_replace("\n", "\r\n", $text);
            $message->setContent($text);
            $message->save();
            $result['s'] = true;
            $result['text'] = Mage::getModel('helpdeskultimate/data_parser')
                ->setText($message->getParsedContent())
                ->prepareToDisplay()
                ->getText();
            $result['quotetext'] = Mage::getModel('helpdeskultimate/data_parser')
                ->setText($message->getParsedContent())
                ->convertToQuoteAsHtml()
                ->getText();
            $result['dbtext'] = $message->getParsedContent();
        }
        echo Zend_Json::encode($result);
        return $this;
    }

    private function _helper($code = null)
    {
        $_helper = 'helpdeskultimate' . ((!is_null($code)) ? ('/' . $code) : '');
        return Mage::helper($_helper);
    }

    private function _createUploader()
    {
        $uploaders = array();
        $maxUploadSize = $this->_helper('config')->getAttachmentsMaxUploadFileSize() * self::SIZE_MEGABYTE;
        foreach ($_FILES as $key => $file) {
            if (isset($file['name']) && !empty($file['name'])) {
                if ($file['size'] > $maxUploadSize || $file['error'] === UPLOAD_ERR_INI_SIZE) {
                    throw(new Mage_Core_Exception($this->__("Uploaded file is too large")));
                }
                /* Starting upload */
                $uploader = new Varien_File_Uploader($key);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $uploaders[] = array('uploader' => $uploader, 'filename' => $file['name']);
            }
        }
        if (!empty($uploaders)) {
            return $uploaders;
        }

        return null;
    }

    protected function _prepareCustomer($ticket)
    {
        $id = $this->getRequest()->getParam('customer_id', false);
        $customer = Mage::getModel('customer/customer');
        if ($id) {
            if (is_numeric($id)) {
                $customer->load($id);
            } elseif (is_string($id)) {
                $email = Mage::helper('helpdeskultimate/imap')->parseEmailAddress($id);
                if ($email) {
                    $name = trim(preg_replace("#[^\s]*@[^\s]*#", "", $id));
                    if (!$name) {
                        $name = $email;
                    }
                    $customer->setEmail($email)->setFirstname($name);
                }
            }
        }
        if (!$customer->getEmail()) {
            throw new Mage_Core_Exception("Please enter at least customer email");
        }
        $ticket->setCustomer($customer);
        
        return $this;
    }
    
    protected function _processReassignedDepartment($ticket)
    {
        $department = Mage::helper('helpdeskultimate')->getDepartment(
            $this->getRequest()->getParam('department_saved_id', 0)
        );
        if ($department->getId() != $ticket->getDepartmentId()) {
            $ticket->setDepartmentId(intval($department->getId()));
            Mage::helper('helpdeskultimate/notify')->ticketReassigned($ticket);
        }

        return $department;
    }

    protected function _addCommentToOrder($orderId, $comment)
    {
        $order =  Mage::getModel('sales/order')->load($orderId);
        if (!is_null($order->getId())) {
            $content = nl2br(html_entity_decode(strip_tags($comment)));
            $order->addStatusHistoryComment($content);
            $order->save();
        }
    }

    private function _addFileNames($uploaders, $obj)
    {

        if (!is_null($uploaders)) {
            $_filenames = null;
            foreach ($uploaders as $uploader) {
                if (!$_filenames) {
                    $_filenames = $uploader['filename'];
                    continue;
                }
                $_filenames .= "|{$uploader['filename']}";
            }
            $obj->setFilename($_filenames);
        }
    }

    protected function _getMessagesInPost()
    {

        if (!empty($_FILES) && is_array($_FILES)) {
            foreach ($_FILES as $file) {
                if (isset($file['name']) && trim($file['name'])) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    private function _sendFile($folderName, $basename = null)
    {
        if (!is_null($this->getRequest()->getParam('basename', null))) {
            $basename = base64_decode($this->getRequest()->getParam('basename'));
        }
        $fileName = base64_decode($this->getRequest()->getParam('file'));
        Mage::helper('helpdeskultimate')->sendFile($folderName . DS . $fileName, $basename);
    }

    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('helpdeskultimate')->checkVersion('1.4.0.0')) {
            return parent::_title($text, $resetIfExists);
        }
        return $this;
    }

}
