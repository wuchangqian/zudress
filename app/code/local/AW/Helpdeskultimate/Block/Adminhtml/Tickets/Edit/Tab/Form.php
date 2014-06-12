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

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        if ($this->getRequest()->getParam('id')) {
            $title = Mage::helper('helpdeskultimate')->__('Reply');
        } else {
            $title = Mage::helper('helpdeskultimate')->__('Ticket');
        }

        $fieldset = $form->addFieldset(
            'helpdeskultimate_form',
            array('legend' => Mage::helper('helpdeskultimate')->__($title))
        );

        $fieldset->addField('id', 'hidden', array(
            'required' => false,
            'name' => 'id'
        ));
        $fieldset->addField('email', 'hidden', array(
            'required' => false,
            'name' => 'email'
        ));

        if (!$this->getRequest()->getParam('id')) {
            // Ticket mode
            $fieldset->addField('title', 'text', array(
                'required' => true,
                'label' => Mage::helper('helpdeskultimate')->__('Title'),
                'title' => Mage::helper('helpdeskultimate')->__('Title'),
                'name' => 'title',
            ));
        }

        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFormData());
        } elseif (Mage::registry('hdu_ticket_data') && $this->getRequest()->getParam('id')) {
            if (!Mage::registry('hdu_ticket_data')->getTitle()) {
                Mage::registry('hdu_ticket_data')->setTitle(
                    Mage::getSingleton('helpdeskultimate/ticket')->load($this->getRequest()->getParam('id'))->getTitle()
                );
            }
            $status = Mage::getSingleton('helpdeskultimate/ticket')->load($this->getRequest()->getParam('id'))
                ->getStatus();
            if ($status == AW_Helpdeskultimate_Model_Status::STATUS_OPEN) {
                $status = AW_Helpdeskultimate_Model_Status::STATUS_WAITING;
            }

            Mage::registry('hdu_ticket_data')->setStatus($status);

            $form->setValues(array_merge(Mage::registry('hdu_ticket_data')->getData(), array(
                'id' => $this->getRequest()->getParam('id'),
                'content' => '',
            )));
        }

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

        $fieldset->addField('content_value', 'editor', array(
            'name' => 'content',
            'label' => Mage::helper('helpdeskultimate')->__('Message'),
            'title' => Mage::helper('helpdeskultimate')->__('Message'),
            'style' => 'width:700px; height:300px;',
            'value' => isset ($data['content']) ? $data['content'] : '',
            'config' => $config
        ));
        $fieldset->addField('add_comment_to_order', 'checkbox', array(
            'label' => '',
            'name' => 'add_comment_to_order',
            'checked' => false,
            'value' => '1',
            'after_element_html' => '&nbsp;<label for="add_comment_to_order">'
                                  . Mage::helper('helpdeskultimate')->__('Add comment to order')
                                  . '</label>'
        ));
        $fieldset->addField('filename', 'file', array(
            'label' => $this->__('File') . ("(" . Mage::getStoreConfig('helpdeskultimate/advanced/maxupload') . " Mb)"),
            'required' => false,
            'name' => 'filename',
        ))->setRenderer(
            $this->getLayout()
                ->createBlock('adminhtml/widget_form_renderer_fieldset')
                ->setTemplate('helpdeskultimate/ticket/edit/file.phtml')
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
