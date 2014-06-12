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

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function getHeaderText()
    {
        $_hduTicketData = Mage::registry('hdu_ticket_data');
        if ($_hduTicketData && $_hduTicketData->getId()) {
            return $this->__(
                "Post Reply to '%s' [#%s]",
                $this->escapeHtml($_hduTicketData->getTitle()),
                $_hduTicketData->getUid()
            );
        } else {
            return $this->__('Create New Ticket');
        }
    }

    public function getBackUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('*/');
    }

    /*CONSTRUCT*/
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'helpdeskultimate';
        $this->_mode = 'edit';
        $this->_controller = 'adminhtml_tickets';

        $this->_updateButton('save', 'label', $this->__('Save'));

        $this->_addButton('saveandemail', array(
             'label'   => $this->__('Save and email'),
             'onclick' => 'saveAndEmail()',
             'class'   => 'save',
        ), -100);

        $this->_addButton('saveandcontinue', array(
             'label'   => $this->__('Save And Continue Edit'),
             'onclick' => 'saveAndContinueEdit()',
             'class'   => 'save',
        ), -100);

        if (Mage::registry('hdu_ticket_data')->isReadOnly()) {
            $this->_removeButton('save');
            $this->_removeButton('delete');
            $this->_removeButton('saveandcontinue');
            $this->_removeButton('saveandemail');
            $this->_removeButton('reset');
        } else {
            $this->_formScripts[] = "
                //variables for JS in skin/adminhtml/[package]/[themes]/aw_helpdeskultimate/js/ticket.js
                var AWHDUAjaxFindOrderUrl = '" . $this->getUrl('*/ticket/ajaxfindorder') . "';
                var AWHDUAjaxGetTemplateContent = '" . $this->getUrl('*/ticket/ajaxgettplcontent') . "';
                var AWHDUUrlToOrderView =  '" . $this->getUrl('adminhtml/sales_order/view') . "';
                var AWHDUUrlToUserSuggest = '" . $this->getUrl('*/ticket/usersuggest') . "';

                var AWHDUCurrentTicketId = '" . Mage::registry('hdu_ticket_data')->getId() . "';

                var AWHDUMessageBlockquoteOpenTitle = '" . $this->__('Click to open quotation') . "';
                var AWHDUMessageBlockquoteCloseTitle = '" . $this->__('Click to close quotation') . "';

                var AWHDUMessageAlertTemplate = '" . $this->__('Please select template') . "';
            ";
        }
    }
}
