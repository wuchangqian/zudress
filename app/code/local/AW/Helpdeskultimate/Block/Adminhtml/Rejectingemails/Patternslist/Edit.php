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

class AW_Helpdeskultimate_Block_Adminhtml_Rejectingemails_Patternslist_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_rejectingemails_patternslist';
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'helpdeskultimate';

        $this->_addButton('saveandcontinueedit', array(
                                                      'label' => $this->__('Save And Continue Edit'),
                                                      'onclick' => 'awhduSaveAndContinueEdit()',
                                                      'class' => 'save',
                                                      'id' => 'awis-save-and-continue'
                                                 ), -200);

        $this->_formScripts[] = "
        function awhduSaveAndContinueEdit() {
            if($('edit_form').action.indexOf('continue/1/')<0)
                $('edit_form').action += 'continue/1/';
            editForm.submit();
        }";
    }

    public function getHeaderText()
    {
        return $this->__('Pattern');
    }
}
