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

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tab_Notes extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'helpdeskultimate_form',
            array('legend' => Mage::helper('helpdeskultimate')->__('Notes'))
        );

        $fieldset->addField('id', 'hidden', array(
            'required' => false,
            'name' => 'id',
        ));

        $fieldset->addField('note', 'editor', array(
            'required' => false,
            'label' => Mage::helper('helpdeskultimate')->__('Note'),
            'title' => Mage::helper('helpdeskultimate')->__('Note'),
            'style' => 'width:700px; height:300px;',
            'name' => 'note',
            'value' => ':)',
        ));

        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFormData());
        } elseif (Mage::registry('hdu_ticket_data')) {
            $form->setValues(
                array(
                     'note' => Mage::registry('hdu_ticket_data')->getNote()
                )
            );
        }
        return parent::_prepareForm();
    }
}
