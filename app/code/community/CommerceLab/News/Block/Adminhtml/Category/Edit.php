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

class CommerceLab_News_Block_Adminhtml_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'clnews';
        $this->_controller = 'adminhtml_category';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('clnews')->__('Save Category'));
        $this->_updateButton('delete', 'label', Mage::helper('clnews')->__('Delete Category'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if ( Mage::registry('clnews_data') && Mage::registry('clnews_data')->getId() ) {
            return Mage::helper('clnews')->__("Edit Category  '%s'",
                $this->htmlEscape(Mage::registry('clnews_data')->getTitle()));
        } else {
            return Mage::helper('clnews')->__('Add Category');
        }
    }
}
