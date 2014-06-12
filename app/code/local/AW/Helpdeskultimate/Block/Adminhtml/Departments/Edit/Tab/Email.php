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

class AW_Helpdeskultimate_Block_Adminhtml_Departments_Edit_Tab_Email
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('Email Settings');
    }

    public function getTabTitle()
    {
        return $this->__('Email Settings');
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

        $fEmail = $form->addFieldset(
            'helpdeskultimate_form_email',
            array('legend' => $this->__('Email Settings'))
        );

        $fEmail->addField('notify', 'select', array(
            'label'  => $this->__('Use email notifications'),
            'name'   => 'notify',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));
        $fEmail->addField('contact', 'text', array(
            'label'    => $this->__('Email'),
            'name'     => 'contact',
            'required' => true,
            'note'     => $this->__('Emails to department will be sent to this address'),
            'class'    => 'validate-uniq-email validate-email',
        ));
        $fEmail->addField('sender', 'select', array(
            'label'  => $this->__('Sender'),
            'name'   => 'sender',
            'note'   => $this->__('This will be used as "From" address in emails sent to department/customer'),
            'values' => Mage::getModel('adminhtml/system_config_source_email_identity')->toOptionArray(),
        ));
        $fEmail->addField('gateways', 'multiselect', array(
            'label'  => $this->__('Use email gateways'),
            'name'   => 'gateways',
            'note'   => $this->__('If none gateway is selected, all of them will be checked'),
            'values' => Mage::getModel('helpdeskultimate/source_gateways')->toOptionArray(),
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
