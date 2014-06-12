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

class AW_Helpdeskultimate_Block_Contacts_Form extends Mage_Core_Block_Template
{
    public function getDepartments()
    {
        $departments = Mage::getModel('helpdeskultimate/department')->getCollection();
        $departments
            ->setActiveFilter()
            ->setVisibilityFilter()
            ->orderByDisplayOrder();
        return $departments;
    }

    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            if (Mage::helper('helpdeskultimate/config')->getConfig('modules/cf_enabled')
                && Mage::helper('helpdeskultimate/config')->getConfig('modules/show_dep_selector')
            ) {
                $this->setTemplate('helpdeskultimate/contacts/form.phtml');
            } else {
                $this->setTemplate('contacts/form.phtml');
            }
        }
    }
}
