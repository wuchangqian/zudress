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


class AW_Helpdeskultimate_Block_Adminhtml_Gateways_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post', 'enctype' => 'multipart/form-data'
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $fGateway = $form->addFieldset('helpdeskultimate_form', array('legend' => $this->__('Email Gateway details')));
        $fGateway->addField('title', 'text', array(
            'label' => $this->__('Title'),
            'name' => 'title',
            'required' => true,
            'note' => ''
        ));
        $fGateway->addField('is_active', 'select', array(
            'label' => $this->__('Enabled'),
            'name' => 'is_active',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));
        $fGateway->addField('protocol', 'select', array(
            'label' => $this->__('Type'),
            'name' => 'protocol',
            'values' => Mage::getModel('helpdeskultimate/source_transport')->toOptionArray()
        ));
        $fGateway->addField('email', 'text', array(
            'label' => $this->__('Gateway Email'),
            'name' => 'email',
            'required' => true,
            'note' => $this->__('An email address for Help Desk to fetch messages from. This address must NOT be used by any other person or system!'),
            'class' => 'validate-uniq-email validate-email'
        ));
        $fGateway->addField('host', 'text', array(
            'label' => $this->__('Gateway Host'),
            'name' => 'host',
            'required' => true,
            'note' => ''
        ));
        $fGateway->addField('login', 'text', array(
            'label' => $this->__('Login'),
            'name' => 'login',
            'required' => true,
            'note' => ''
        ));
        $fGateway->addField('password', 'password', array(
            'label' => $this->__('Password'),
            'name' => 'password',
            'required' => true,
            'note' => ''
        ));
        $fGateway->addField('port', 'text', array(
            'label' => $this->__('Port'),
            'name' => 'port',
            'required' => false,
            'note' => $this->__('110 for POP3, 995 for POP3-SSL, 143 for IMAP-TLS and 993 for IMAP-SSL by default')
        ));
        $fGateway->addField('secure', 'select', array(
            'label' => $this->__('Use SSL/TLS'),
            'name' => 'secure',
            'values' => Mage::getModel('helpdeskultimate/source_SSL')->toOptionArray()
        ));

        $fGateway->addField('delete_message', 'select', array(
            'label' => $this->__('Delete Emails From Host'),
            'name' => 'delete_message',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        # Test connection button
        $fGateway->addType('testconnection', 'AW_Helpdeskultimate_Model_Form_Element_Testconnection');
        $fGateway->addField('test_connection', 'testconnection', array(
            'label' => '',
            'name' => 'test_connection_button',
        ));

        $_session = Mage::getSingleton('adminhtml/session');
        if ($_session->getHelpdeskultimateData()) {
            $form->setValues($_session->getHelpdeskultimateData());
            $_session->setHelpdeskultimateData(null);
        } elseif (Mage::registry('gateway') && Mage::registry('gateway')->getId()) {
            $form->setValues(Mage::registry('gateway')->getData());
        } else {
            $form->setValues(
                array('host' => 'localhost', 'port' => 110)
            );
        }
        return parent::_prepareForm();

    }
}
