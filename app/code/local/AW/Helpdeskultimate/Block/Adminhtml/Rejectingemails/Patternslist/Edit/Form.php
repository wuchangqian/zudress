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

class AW_Helpdeskultimate_Block_Adminhtml_Rejectingemails_Patternslist_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/patternsave', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post'
        ));
        $_data = Mage::helper('helpdeskultimate')->getRejectedFormData($this->getRequest()->getParam('id'));
        if (!is_object($_data)) {
            $_data = new Varien_Object($_data);
        }

        $_fieldset = $_form->addFieldset('general_fieldset', array('legend' => $this->__('General Information')));
        $_fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => $this->__('Name'),
            'required' => true,
        ));
        if ($_data->getData('is_active') === null) {
            $_data->setData('is_active', 1);
        }

        $_fieldset->addField('is_active', 'select', array(
            'name' => 'is_active',
            'label' => $this->__('Status'),
            'required' => true,
            'values' => Mage::getModel('helpdeskultimate/source_status')->toOptionArray(),
        ));
        $_fieldset->addField('scope', 'multiselect', array(
            'name' => 'scope',
            'label' => $this->__('Scope'),
            'required' => true,
            'values' => Mage::getModel('helpdeskultimate/source_rejectedemails_scope')->toOptionArray(),
        ));
        $_fieldset->addField('pattern', 'text', array(
            'name' => 'pattern',
            'label' => $this->__('Pattern'),
            'required' => true,
        ));

        $_form->setUseContainer(TRUE);
        $_form->setValues($_data);
        $this->setForm($_form);
        return parent::_prepareForm();
    }
}
