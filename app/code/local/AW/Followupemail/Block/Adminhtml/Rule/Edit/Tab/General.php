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
 * @package    AW_Followupemail
 * @version    3.5.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Followupemail_Block_Adminhtml_Rule_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $data = Mage::registry('followupemail_data');

        if (is_object($data)) $data = $data->getData();

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('general', array('legend' => $this->__('Rule')));

        # title field
        $fieldset->addField('title', 'text', array(
            'label' => $this->__('Title'),
            'name' => 'title',
            'required' => true,
        ));

        # is_active field
        $fieldset->addField('is_active', 'select', array(
            'label' => $this->__('Status'),
            'name' => 'is_active',
            'values' => AW_Followupemail_Model_Source_Rule_Status::toOptionArray(),
        ));

        # active from field
        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        try {
            if (isset($data['active_from']) && !empty($data['active_from'])) {
                $data['active_from'] = Mage::app()->getLocale()->date($data['active_from'], Varien_Date::DATETIME_INTERNAL_FORMAT);
            }
        } catch (Exception $e) {
            unset ($data['active_from']);
            throw $e;
        }
        $fieldset->addField('active_from', 'date', array(
            'label' => Mage::helper('followupemail')->__('Rule active from'),
            'name' => 'active_from',
            'title' => $this->__('Rule active from'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => $outputFormat,
            'input_format' => Varien_Date::DATETIME_INTERNAL_FORMAT,
            'time' => true,
            'required' => false,
            'note' => $this->__('Leave empty for unlimited usage'),
        ));

        # active to field
        try {
            if (isset($data['active_to']) && !empty($data['active_to'])) {
                $data['active_to'] = Mage::app()->getLocale()->date($data['active_to'], Varien_Date::DATETIME_INTERNAL_FORMAT);
            }
        } catch (Exception $e) {
            unset ($data['active_to']);
            throw $e;
        }
        $fieldset->addField('active_to', 'date', array(
            'label' => Mage::helper('followupemail')->__('Rule active to'),
            'name' => 'active_to',
            'title' => $this->__('Rule active to'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => $outputFormat,
            'input_format' => Varien_Date::DATETIME_INTERNAL_FORMAT,
            'time' => true,
            'required' => false,
            'note' => $this->__('Leave empty for unlimited usage'),
        ));

        # event_type field
        $fieldset->addField('event_type', 'select', array(
            'label' => $this->__('Event'),
            'name' => 'event_type',
            'values' => AW_Followupemail_Model_Source_Rule_Types::toOptionArray(),
            'required' => true,
            'onchange' => 'checkEventType()',
        ));

        # cancel_events field
        $fieldset->addField('cancel_events', 'multiselect', array(
            'label' => $this->__('Cancellation events'),
            'name' => 'cancel_events[]',
            'values' => AW_Followupemail_Model_Source_Rule_Types::toOptionArray(TRUE),
            'note' => $this->__('Once selected event(s) happen they cancel email sending for the object'),
        ));

        # customer_groups field
        $fieldset->addField('customer_groups', 'multiselect', array(
            'name' => 'customer_groups[]',
            'label' => $this->__('Customer groups'),
            'title' => $this->__('Customer groups'),
            'required' => true,
            'values' => AW_Followupemail_Model_Source_Customer_Group::toOptionArray(),
        ));

        # sku field
        $fieldset->addField('sku', 'text', array(
            'label' => $this->__('SKU'),
            'name' => 'sku',
            'note' => $this->__('Separate multiple SKUs by commas'),
        ));

        # sale_amount_value field
        $fieldset->addField('sale_amount_value', 'select', array(
            'label' => $this->__('Sale amount'),
            'name' => 'sale_amount_value',
            'value' => $data['sale_amount_value'],
            'condition' => $data['sale_amount_condition'],
            'conditions' => Mage::getModel('followupemail/source_rule_saleamount')->toOptionArray(true),
        ));
        $form->getElement('sale_amount_value')->setRenderer($this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_details_saleamount'));

        # chain field
        $fieldset->addField('chain', 'text', array(
            'label' => $this->__('Email chain'),
            'name' => 'chain',
            'required' => true,
            'class' => 'requried-entry'
        ));
        $form->getElement('chain')->setRenderer($this->getLayout()->createBlock('followupemail/adminhtml_rule_edit_tab_details_chain'));

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}