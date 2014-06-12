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

class AW_Helpdeskultimate_Block_Customer_Replyform extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();

        $data = Mage::getSingleton('helpdeskultimate/session')->getFormData(true);
        $data = new Varien_Object($data);

        $this->setTemplate('helpdeskultimate/replyform.phtml')
            ->assign('data', $data)
            ->assign('messages', Mage::getSingleton('helpdeskultimate/session')->getMessages(true));
    }

    /**
     * Retrives CAN SHOW FORM flag
     * @return boolean
     */
    public function canShowForm()
    {
        return Mage::helper('helpdeskultimate')->canShowFrontendForms();
    }

    /**
     * TODO check if this method is realy needed
     * @return
     */
    public function getTicketInfo()
    {
        return $this->getTicket();
    }

    public function getTicket()
    {
        if (!$this->getData('ticket')) {
            $ticket = Mage::getModel('helpdeskultimate/ticket');
            if ($this->getRequest()->getParam('id')) {
                $this->setTicket($ticket->load($this->getRequest()->getParam('id')));
            }
        }
        return $this->getData('ticket');
    }

    public function getAction()
    {
        return Mage::getUrl('helpdeskultimate/customer/postreply', array('id' => $this->getTicket()->getId()));
    }

    public function getCloseTicketAction()
    {
        $params = array(
            'id' => $this->getTicket()->getId()
        );
        if ($uid = $this->getRequest()->getParam('uid', false)) {
            $params['uid'] = $uid;
        }
        if ($key = $this->getRequest()->getParam('key', false)) {
            $params['key'] = $key;
        }
        return Mage::getUrl('helpdeskultimate/customer/ticketclose', $params);
    }

    public function isExternal()
    {
        return !!$this->getRequest()->getParam('uid');
    }


}
