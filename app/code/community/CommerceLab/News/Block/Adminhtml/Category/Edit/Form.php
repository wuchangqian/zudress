<?php
/**
 * CommerceLab Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the CommerceLab License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://commerce-lab.com/LICENSE.txt
 *
 * @category   CommerceLab
 * @package    CommerceLab_News
 * @copyright  Copyright (c) 2011 CommerceLab Co. (http://commerce-lab.com)
 * @license    http://commerce-lab.com/LICENSE.txt
 */

class CommerceLab_News_Block_Adminhtml_Category_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
        ));

        $fieldset = $form->addFieldset('category_form',
            array('legend'=>Mage::helper('clnews')->__('Category Information')));

        $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('clnews')->__('Title'),
            'title'     => Mage::helper('clnews')->__('Title'),
            'name'      => 'title',
            'required'  => true
        ));

        $fieldset->addField('url_key', 'text', array(
            'label'     => Mage::helper('clnews')->__('URL Key'),
            'title'     => Mage::helper('clnews')->__('URL Key'),
            'name'      => 'url_key',
            'required'  => true
        ));

        $fieldset->addField('sort_order', 'text', array(
            'label'     => Mage::helper('clnews')->__('Sort Order'),
            'name'      => 'sort_order',
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('cms')->__('Store View'),
                'title'     => Mage::helper('cms')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }

        $fieldset->addField('meta_keywords', 'editor', array(
            'name' => 'meta_keywords',
            'label' => Mage::helper('clnews')->__('Keywords'),
            'title' => Mage::helper('clnews')->__('Meta Keywords'),
        ));

        $fieldset->addField('meta_description', 'editor', array(
            'name' => 'meta_description',
            'label' => Mage::helper('clnews')->__('Description'),
            'title' => Mage::helper('clnews')->__('Meta Description'),
        ));

        if ( Mage::getSingleton('adminhtml/session')->getNewsData() ) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getNewsData());
            Mage::getSingleton('adminhtml/session')->setNewsData(null);
        } elseif ( Mage::registry('clnews_data') ) {
            $form->setValues(Mage::registry('clnews_data')->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
