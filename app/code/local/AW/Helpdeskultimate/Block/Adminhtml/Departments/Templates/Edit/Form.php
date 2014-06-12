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

class AW_Helpdeskultimate_Block_Adminhtml_Departments_Templates_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        try {
            $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            $config->setData(Mage::helper('helpdeskultimate')->recursiveReplace(
                '/helpdeskultimate_admin/',
                '/' . (string)Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName') . '/',
                $config->getData()
            ));
        } catch (Exception $ex) {
            $config = null;
        }

        $fGeneral = $form->addFieldset('template_details', array('legend' => $this->__('Template details')));
        $fGeneral->addField('id', 'hidden', array(
            'required' => false,
            'name'     => 'id',
        ));
        $fGeneral->addField('enabled', 'select', array(
            'label'  => $this->__('Active'),
            'name'   => 'enabled',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));
        $fGeneral->addField('name', 'text', array(
            'label'    => $this->__('Title'),
            'name'     => 'name',
            'required' => true,
            'note'     => $this->__('Service name of template'),
        ));
        $fGeneral->addField('content', 'editor', array(
            'label'  => Mage::helper('helpdeskultimate')->__('Content'),
            'name'   => 'content',
            'style'  => 'width:500px; height:400px;',
            'config' => $config,
        ));

        $_session = Mage::getSingleton('adminhtml/session');
        if ($_session->getHelpdeskultimateData()) {
            $form->setValues($_session->getHelpdeskultimateData());
            $_session->setHelpdeskultimateData(null);
        } elseif (Mage::registry('template')) {
            $form->setValues(Mage::registry('template')->getData());
        }
        return parent::_prepareForm();
    }
}
