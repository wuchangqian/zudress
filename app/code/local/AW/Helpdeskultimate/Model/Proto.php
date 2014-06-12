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


class AW_Helpdeskultimate_Model_Proto extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FAILED = 'failed';

    protected static $_gatewaysDepartments = null;

    protected function _construct()
    {
        $this->_init('helpdeskultimate/proto');
        $this->_initGatewaysDepartments();
    }

    /**
     * Indicates if protot can be converted to ticket message
     * @return bool
     */
    public function canBeConvertedToMessage()
    {
        return $this->_loadTicket() instanceof AW_Helpdeskultimate_Model_Ticket;
    }

    /**
     * Converts proto to message from tickets
     * @return
     */
    public function convertToMessage()
    {
        $_notifyDepartment = $_notifyCustomer = 0;
        $message = Mage::getModel('helpdeskultimate/message');
        if ($ticket = $this->_loadTicket()) {

            // Detect author of message
            if ($department = $this->_findDepartment($this->getFromEmail(), $ticket->getStoreId())) {
                // Message from department
                if ($ticket->getDepartmentId() != $department->getId()) {
                    // Ticket is taken by other department
                    $ticket->setDepartment($department);
                    $this->log(
                        "Ticket #%s is reassigned to department \"%s\"",
                        $ticket->getUid(),
                        $department->getName()
                    );
                    Mage::helper('helpdeskultimate/notify')->ticketReassigned($ticket);
                }
                $ticket
                    ->setIsChangedByCustomer(false)
                    ->setStatus(AW_Helpdeskultimate_Model_Status::STATUS_WAITING)
                    ->save();
                $customer = $ticket->getCustomer();
                $message
                    ->setAuthorName($department->getName())
                    ->setDepartmentId($department->getId());
                $_notifyCustomer = 1;
            } else {
                // Found ticket
                $customer = $this->getCustomer();
                // Message from customer
                $message
                    ->setAuthorName($customer->getName())
                    ->setCustomerId($customer->getId());

                $ticket
                    ->setIsChangedByCustomer(true)
                    ->setStatus(AW_Helpdeskultimate_Model_Status::STATUS_OPEN)
                    ->save();
                $_notifyDepartment = 1;
            }

            $message
                ->setFromEmail(1)
                ->setTicketId($ticket->getId())
                ->setContent($this->getContent())
                ->setContentType($this->getContentType())
                ->save();

            $ticket->setTotalReplies($ticket->getMessagesCount());
            $ticket->save();

            if ($this->getFilename()) {
                @mkdir($message->getFolderName());
                foreach (explode('|', $this->getFilename()) as $file) {
                    $file = Mage::helper('helpdeskultimate')->getRealFileName($this->getFolderName(), $file);
                    if ($file)
                        copy($this->getFolderName() . $file, $message->getFolderName() . $file);
                }
                $message->setFilename($this->getFilename())->save();
            }

            if ($_notifyDepartment) {
                Mage::helper('helpdeskultimate/notify')->ticketReplyToAdmin($message);
            }
            if ($_notifyCustomer) {
                Mage::helper('helpdeskultimate/notify')->ticketReplyToCustomer($message, $customer);
            }
        } else {
            throw new AW_Core_Exception("Can't find ticket for proto #%s while converting to ticket", $this->getId());
        }

        return $message;
    }

    /**
     * Converts proto to new ticket
     * @return AW_Helpdeskultimate_Model_Ticket
     */
    public function convertToTicket()
    {
        $ticket = Mage::getModel('helpdeskultimate/ticket');
        $ticket
            ->setContent($this->getContent())
            ->setPriority($this->getPriority())
            ->setContentType($this->getContentType())
            ->setTitle($this->getSubject())
            ->setOrderId($this->getOrderId());

        if ($this->getData('created_by')) {
            $ticket->setCreatedBy($this->getData('created_by'));
        } elseif (($department = $this->_findDepartment($this->getFromEmail()))
                || (($this->getSource() == 'order' || $this->getSource() == 'email') && $this->_findTicketIdentity())) {
            // Ticket is initiated by department or from order
            $ticket->setCreatedBy(AW_Helpdeskultimate_Model_Ticket::CREATED_BY_ADMIN);
        } else {
            // Ticket is initiated by customer
            $ticket->setCreatedBy(AW_Helpdeskultimate_Model_Ticket::CREATED_BY_CUSTOMER);
        }

        if (!$this->getDepartmentId()) {
            $department = $this->getDefaultDepartment();
        } else {
            $department = Mage::getModel('helpdeskultimate/department')->load($this->getDepartmentId());
        }
        $ticket->setCustomer($this->getCustomer());

        /**
         * detect store id
         */
        $ticket->setStoreId(0);
        if ($this->getStoreId()) {
            $ticket->setStoreId($this->getStoreId());
        } elseif (!Mage::app()->isSingleStoreMode() && $department->getPrimaryStoreId()) {
            $ticket->setStoreId($department->getPrimaryStoreId());
            $this->log("Assigned ticket to store #%s", $department->getPrimaryStoreId());
        }

        $ticket
            ->setDepartment($department)->save();
        $this->log("Assigned ticket #%s to department \"%s\"", $ticket->getUid(), $department->getName());

        if ($this->getFilename()) {
            @mkdir($ticket->getFolderName());
            foreach (explode('|', $this->getFilename()) as $file) {
                $file = Mage::helper('helpdeskultimate')->getRealFileName($this->getFolderName(), $file);
                if ($file)
                    copy($this->getFolderName() . $file, $ticket->getFolderName() . $file);
            }
            $ticket->setFilename($this->getFilename())->save();
        }

        // Notifying 
        Mage::helper('helpdeskultimate/notify')->ticketNew($ticket, $ticket->getCustomer());
        return $ticket;
    }

    /**
     * Tryes to resolve department by email. If not found return bool false
     * @param string $email
     * @return AW_Helpdeskultimate_Model_Department|bool false
     */
    protected function _findDepartment($email, $storeId = null)
    {
        $email = strtolower($email);
        $departments = Mage::getModel('helpdeskultimate/department')->getCollection()->setContactFilter($email)->load();

        foreach ($departments as $item) {
            if ($item->getEnabled() && ($storeId === null || $storeId == $item->getPrimaryStoreId())) {
                return $item;
            }
        }
        return false;
    }
    
    /**
     * Match standard ticket identifier from subject for e.g [#ZXF-2344]
     * @return bool 
     */    
    protected function _findTicketIdentity()
    {
        $subject = trim($this->getSubject());
        if (empty($subject)) {
            return true;
        }
        
        return preg_match("/\[#\S+?-\S+?\].+$/isu", $this->getSubject());
    }

    /**
     * Returns gateway.
     * @return AW_Helpdeskultimate_Model_Gateway
     */
    public function getGateway()
    {
        if (!$this->getData('gateway')) {
            Mage::getModel('helpdeskultimate/gateway')->load($this->getGatewayId());
        }
        return $this->getData('gateway');
    }

    /**
     * Tryes to guess department
     * @return AW_Helpdeskultimate_Model_Department
     */
    public function getDefaultDepartment()
    {

        if ($this->getGatewayId()) {
            if (isset(self::$_gatewaysDepartments[$this->getGatewayId()])) {
                //return first department for this gateway
                $data = self::$_gatewaysDepartments[$this->getGatewayId()];
                return Mage::getModel('helpdeskultimate/department')->load($data[0]);
            } else {
                $activeDepartments = Mage::getModel('helpdeskultimate/department')->getCollection()->addActiveFilter();
                foreach ($activeDepartments as $department) {
                    return $department;
                }
            }
        } else {
            // No gateway id. Try to restore by store id
            $dep = Mage::getModel('helpdeskultimate/department')->loadByPrimaryStoreId($this->getStoreId());
            if ($dep->getId()) {
                return $dep;
            } else {
                $activeDepartments = Mage::getModel('helpdeskultimate/department')->getCollection()->addActiveFilter();
                foreach ($activeDepartments as $department) {
                    return $department;
                }
            }
        }
        throw new AW_Core_Exception("Failed to get any department.");
        $this->log("No departments found for gateway #%s", $this->getGatewayId());
        return Mage::getModel('helpdeskultimate/department');
    }

    /**
     * Returns founded customer or preset entity
     * @return Mage_Customer_Model_Customer | Varien_Object
     */
    public function getCustomer()
    {
        return $this->getSourceInstance()->getCustomer($this);
    }

    /**
     * Returns parsed email
     * @return Mage_Customer_Model_Customer | Varien_Object
     */
    public function getFromEmail()
    {
        return $this->getSourceInstance()->getFromEmail($this);
    }

    /**
     * Initializes gateway=>department links
     * @return array
     */
    protected function _initGatewaysDepartments()
    {
        if (!self::$_gatewaysDepartments) {
            $gateways = array();
            $globalDeps = array();
            $departments = Mage::getModel('helpdeskultimate/department')->getCollection()->addActiveFilter();
            foreach ($departments as $department) {
                if (!$department->usesAllGateways()) {
                    foreach ($department->getGateways() as $gw) {
                        @$gateways[$gw][] = $department->getId();
                    }
                } else {
                    //department uses all gateways
                    $globalDeps[] = $department->getId();
                }
            }
            if (sizeof($globalDeps)) {
                foreach (Mage::getModel('helpdeskultimate/gateway')->getCollection()->addActiveFilter() as $gateway) {
                    foreach ($globalDeps as $gdId) {
                        @$gateways[$gateway->getId()][] = $gdId;
                    }
                }
            }
            self::$_gatewaysDepartments = $gateways;
        }
    }

    /**
     * Tryes to load ticket
     * @return AW_Helpdeskultimate_Model_Ticket | bool false
     */
    protected function _loadTicket()
    {
        if (!$this->getData('_ticket') && $this->getData('_ticket') !== false) {
            $this->setData('_ticket', false);
            if (!($uid = $this->_parseTicketUid($this->getSubject()))) {
                return false;
            }
            if ($ticket = Mage::getModel('helpdeskultimate/ticket')->loadByUid($uid)) {
                if ($ticket->getId()) {
                    $this->setData('_ticket', $ticket);
                    return $ticket;
                }
            }
        }

        return $this->getData('_ticket');
    }

    /**
     * Returns ticket UID from subject
     * @param string $subject
     * @return string|bool
     */
    protected function _parseTicketUid($subject)
    {
        if (preg_match("/\[#([a-z]{3}-[0-9]{5})\]/i", $subject, $matches)) {
            return strtoupper(@$matches[1]);
        } else
            return false;
    }

    /**
     * Returns folder for according proto
     * @return string
     */
    public function getFolderName()
    {
        $path = Mage::getBaseDir('media') . DS . 'helpdeskultimate' . DS . 'proto-' . $this->getId() . DS;
        return $path;
    }

    /**
     * Save filename
     * @return string
     */
    public function getFilename()
    {
        // Override to clean up zombie files
        if (isset($this->_data['filename'])) {
            $fn = $this->_data['filename'];
        } else {
            return '';
        }
        foreach (explode('|', $fn) as $file) {
            $file = Mage::helper('helpdeskultimate')->getRealFileName($this->getFolderName(), $file);
            if ($file)
                return $fn;
        }
        return '';
    }

    /**
     * Returns source instance
     * @return AW_Helpdeskultimate_Model_Proto_Source_Abstract
     */
    public function getSourceInstance()
    {
        if (!$this->getData('source_instance')) {
            $this->setData('source_instance', Mage::getModel('helpdeskultimate/proto_source_' . $this->getSource()));
        }
        return $this->getData('source_instance');
    }

    /**
     * Validates proto
     * @return array | bool
     */
    public function validate()
    {
        $errors = array();
        $helper = Mage::helper('helpdeskultimate');

        if (!Zend_Validate::is($this->getSubject(), 'NotEmpty')) {
            $errors[] = $helper->__('Please specify title<br/>');
        }

        if (!Zend_Validate::is($this->getContent(), 'NotEmpty')) {
            $errors[] = $helper->__("Content can't be empty");
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    private function log()
    {
        $args = func_get_args();
        call_user_func_array(array(Mage::helper('helpdeskultimate/logger'), 'log'), array_values($args));
        return $this;
    }
}
