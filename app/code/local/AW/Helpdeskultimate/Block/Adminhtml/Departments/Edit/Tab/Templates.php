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

class AW_Helpdeskultimate_Block_Adminhtml_Departments_Edit_Tab_Templates
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    const TO_ADMIN_NEW_EMAIL         = 'helpdeskultimate_to_admin_new_email';
    const TO_ADMIN_REPLY_EMAIL       = 'helpdeskultimate_to_admin_reply_email';
    const TO_CUSTOMER_NEW_EMAIL      = 'helpdeskultimate_to_customer_new_email';
    const TO_CUSTOMER_REPLY_EMAIL    = 'helpdeskultimate_to_customer_reply_email';
    const TO_ADMIN_REASSIGN_EMAIL    = 'helpdeskultimate_to_admin_reassign_email';
    const TO_CUSTOMER_REASSIGN_EMAIL = 'helpdeskultimate_to_customer_reassign_email';
    const NEW_FROM_ADMIN_TO_CUSTOMER = 'helpdeskultimate_new_from_admin_to_customer';

    public function getTabLabel()
    {
        return $this->__('Email Templates');
    }

    public function getTabTitle()
    {
        return $this->__('Email Templates');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $this->_initForm();
        return parent::_prepareForm();
    }

    protected function _initForm()
    {
        $form = new Varien_Data_Form();

        $fTemplates = $form->addFieldset(
            'helpdeskultimate_form_templates',
            array('legend' => $this->__('Email Templates'))
        );

        $fTemplates->addField('to_admin_new_email', 'select', array(
            'label'  => $this->__('New Ticket Admin Template'),
            'name'   => 'to_admin_new_email',
            'note'   => $this->__('New ticket, notification for support'),
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')
                ->setPath(self::TO_ADMIN_NEW_EMAIL)
                ->toOptionArray(),
        ));
        $fTemplates->addField('to_admin_reply_email', 'select', array(
            'label'  => $this->__('Ticket Reply Admin Template'),
            'name'   => 'to_admin_reply_email',
            'note'   => $this->__('Reply in a ticket, notification for support'),
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')
                ->setPath(self::TO_ADMIN_REPLY_EMAIL)
                ->toOptionArray(),
        ));
        $fTemplates->addField('to_customer_new_email', 'select', array(
            'label'  => $this->__('New Ticket Customer Template'),
            'name'   => 'to_customer_new_email',
            'note'   => $this->__('New ticket, notification for customer'),
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')
                ->setPath(self::TO_CUSTOMER_NEW_EMAIL)
                ->toOptionArray(),
        ));
        $fTemplates->addField('new_from_admin_to_customer', 'select', array(
            'label'  => $this->__('New Ticket Customer Template(initiated by admin)'),
            'name'   => 'new_from_admin_to_customer',
            'note'   => $this->__('New ticket has been created by support, notification for customer'),
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')
                ->setPath(self::NEW_FROM_ADMIN_TO_CUSTOMER)
                ->toOptionArray(),
        ));
        $fTemplates->addField('to_customer_reply_email', 'select', array(
            'label'  => $this->__('Ticket Reply Customer Template'),
            'name'   => 'to_customer_reply_email',
            'note'   => $this->__('Reply in a ticket, notification for customer'),
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')
                ->setPath(self::TO_CUSTOMER_REPLY_EMAIL)
                ->toOptionArray(),
        ));
        $fTemplates->addField('to_admin_reassign_email', 'select', array(
            'label'  => $this->__('Ticket Reassign Template'),
            'name'   => 'to_admin_reassign_email',
            'note'   => $this->__('Ticket reassignation, notification for support'),
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')
                ->setPath(self::TO_ADMIN_REASSIGN_EMAIL)
                ->toOptionArray(),
        ));
        $fTemplates->addField('to_customer_reassign_email', 'select', array(
            'label'  => $this->__('Ticket Reassign Template Sending to Customer'),
            'name'   => 'to_customer_reassign_email',
            'note'   => $this->__('Ticket reassignation, notification for customer'),
            'values' => Mage::getModel('adminhtml/system_config_source_email_template')
                ->setPath(self::TO_CUSTOMER_REASSIGN_EMAIL)
                ->toOptionArray(),
        ));

        if (Mage::getSingleton('adminhtml/session')->getHelpdeskultimateData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getHelpdeskultimateData());
            Mage::getSingleton('adminhtml/session')->setHelpdeskultimateData(null);
        } elseif (Mage::registry('department')) {
            $form->setValues(Mage::registry('department')->getData());
        }

        $this->setForm($form);
    }
}
