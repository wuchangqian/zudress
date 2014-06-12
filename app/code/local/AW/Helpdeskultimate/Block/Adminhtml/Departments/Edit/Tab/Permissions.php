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

class AW_Helpdeskultimate_Block_Adminhtml_Departments_Edit_Tab_Permissions
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('Admin Permissions');
    }

    public function getTabTitle()
    {
        return $this->__('Admin Permissions');
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

        $fieldset = $form->addFieldset(
            'helpdesk_admin_roles',
            array('legend' => $this->__('Admin Permissions'))
        );

        $fieldset->addField('allowed_roles', 'multiselect', array(
            'name'   => 'allowed_roles[]',
            'title'  => $this->__('Allowed Roles'),
            'label'  => $this->__('Allowed Roles'),
            'values' => Mage::getResourceModel('admin/roles_collection')->toOptionArray(),
        ));

        if (Mage::getSingleton('adminhtml/session')->getHelpdeskultimateData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getHelpdeskultimateData());
            Mage::getSingleton('adminhtml/session')->setHelpdeskultimateData(null);
        } elseif (Mage::registry('department')) {
            $deparment = Mage::registry('department');
            $values = array();
            if (!is_null($deparment->getId())) {
                $permissionsCollection = Mage::getModel('helpdeskultimate/department_permissions')->getCollection();
                foreach ($permissionsCollection as $item) {
                    $allowedDepartments = explode(',', $item->getValue());
                    if (in_array($deparment->getId(), $allowedDepartments)) {
                        $values['allowed_roles'][] = $item->getRoleId();
                    }
                }
            }
            $form->setValues($values);
        }
        $this->setForm($form);
    }
}
