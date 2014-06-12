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


class AW_Helpdeskultimate_Block_Adminhtml_Permissions_Tab_Departments
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('Help Desk Departments');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    public function _beforeToHtml()
    {
        $this->_initForm();

        return parent::_beforeToHtml();
    }

    protected function _initForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            array('legend'=>Mage::helper('adminhtml')->__('Allowed Departments'))
        );

        $fieldset->addField('allowed_departments', 'multiselect', array(
            'name' => 'allowed_departments[]',
            'title' => Mage::helper('helpdeskultimate')->__('Allowed Departments'),
            'label' => Mage::helper('helpdeskultimate')->__('Allowed Departments'),
            'values' => Mage::getModel('helpdeskultimate/source_departments')->toOptionArray()
        ));

        $role = Mage::registry('current_role');
        $values = array();
        if (!is_null($role->getId())) {
            $departmentPermissions = Mage::getModel('helpdeskultimate/department_permissions');
            $departmentPermissions->loadByRoleId($role->getId());
            if (!is_null($departmentPermissions->getId())) {
                $values['allowed_departments'] = $departmentPermissions->getValue();
            }
        }

        $form->setValues($values);
        $this->setForm($form);
    }
}