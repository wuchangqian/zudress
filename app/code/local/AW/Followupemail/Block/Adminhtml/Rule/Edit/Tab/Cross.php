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
 * @package    AW_Followupemail
 * @version    3.5.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Cross extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('cross', array('legend' => $this->__('Cross-sells')));

        $fieldset->addField('cross_active', 'select', array(
            'label' => $this->__('Include cross-sells in email'),
            'name' => 'cross_active',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('cross_source', 'select', array(
            'label' => $this->__('Cross-sells source'),
            'name' => 'cross_source',
            'values' => AW_Followupemail_Model_Source_Rule_Cross::toOptionArray(),
        ));

        if ($data = Mage::registry('followupemail_data')) $form->setValues($data);

        return parent::_prepareForm();
    }
}
