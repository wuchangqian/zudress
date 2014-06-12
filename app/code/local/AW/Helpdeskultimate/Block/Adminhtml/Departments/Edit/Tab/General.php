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

class AW_Helpdeskultimate_Block_Adminhtml_Departments_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('General');
    }

    public function getTabTitle()
    {
        return $this->__('General');
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

        $fGeneral = $form->addFieldset(
            'helpdeskultimate_department_general_form',
            array('legend' => $this->__('Department Details'))
        );

        $fGeneral->addField('id', 'hidden', array(
            'required' => false,
            'name'     => 'id',
        ));
        $fGeneral->addField('enabled', 'select', array(
            'label'  => $this->__('Active'),
            'name'   => 'enabled',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));
        $fGeneral->addField('visible_on', 'multiselect', array(
            'name'   => 'visible_on[]',
            'title'  => $this->__('Visible on'),
            'label'  => $this->__('Visible on'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
        ));
        $fGeneral->addField('name', 'text', array(
            'label'    => $this->__('Title'),
            'name'     => 'name',
            'required' => true,
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $fGeneral->addField('primary_store_id', 'select', array(
                'label'  => $this->__('Primary for store'),
                'name'   => 'primary_store_id',
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(true, false),
            ));
        }

        $fGeneral->addField('display_order', 'text', array(
            'label' => $this->__('Sort Order'),
            'name'  => 'display_order',
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
