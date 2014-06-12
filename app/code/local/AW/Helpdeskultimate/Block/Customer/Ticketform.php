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


class AW_Helpdeskultimate_Block_Customer_Ticketform extends Mage_Core_Block_Template
{
    const TEMPLATE_PATH = 'helpdeskultimate/ticketform.phtml';

    public function __construct()
    {
        parent::__construct();
        $_session = Mage::getSingleton('helpdeskultimate/session');
        $data = new Varien_Object($_session->getFormData(true));
        $this->setTemplate(self::TEMPLATE_PATH)
            ->assign('data', $data)
            ->assign('messages', $_session->getMessages(true));
    }

    public function getAction()
    {
        return Mage::getUrl('helpdeskultimate/customer/new');
    }

    public function isExternal()
    {
        return !!$this->getRequest()->getParam('uid');
    }

    /**
     * Retrives back url in account dashboard
     *
     * @return string
     */
    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/');
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
     * Retring escaped url protection for olfd versions of Magento
     * @param string $data
     * @return string
     */
    public function escapeUrl($data)
    {
        if (method_exists(new Mage_Core_Block_Template(), 'escapeUrl')) {
            return parent::escapeUrl($data);
        } else {
            return $data;
        }
    }

    public function getDepartments()
    {
        $departments = Mage::getModel('helpdeskultimate/department')->getCollection();
        $departments
            ->setActiveFilter()
            ->setVisibilityFilter()
            ->orderByDisplayOrder();
        return $departments;
    }
}
