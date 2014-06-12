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

class AW_Helpdeskultimate_Block_Adminhtml_Departments_Templates_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected function _prepareLayout()
    {
        $this->setChild('form', $this->getLayout()->createBlock($this->_blockGroup . '/' . $this->_mode . '_form'));
        return parent::_prepareLayout();
    }

    public function __construct()
    {

        $this->_objectId = 'attribute_id';
        $this->_controller = 'adminhtml_departments_templates';
        $this->_blockGroup = 'helpdeskultimate';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('catalog')->__('Save template'));

        if (!$this->getRequest()->getParam('id')) {
            $this->_removeButton('delete');
        } else {
            $this->_updateButton('delete', 'onclick', "deleteConfirm("
                . "'" . Mage::helper('adminhtml')->__('Are you sure you want to do this?') . "', "
                . "'" . $this->getUrl('*/*/delete/id/' . $this->getRequest()->getParam('id')) . "')"
            );
        }
    }

    public function getHeaderText()
    {
        if ($this->getEntity()->getId()) {
            return $this->__('Edit Quick Response "%s"', $this->htmlEscape($this->getEntity()->getName()));
        } else {
            return $this->__('New Quick Response');
        }

    }

    public function getEntity()
    {
        return Mage::registry('template');
    }


    public function getSaveUrl()
    {
        return $this->getUrl('*/' . $this->_controller . '/save', array('_current' => true, 'back' => null));
    }
}
