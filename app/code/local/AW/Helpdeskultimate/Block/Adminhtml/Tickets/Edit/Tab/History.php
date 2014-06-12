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

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tab_History extends Mage_Adminhtml_Block_Widget
{
    /**
     *  edit tab template
     */
    const HISTORY_TEMPLATE = "helpdeskultimate/ticket/edit/history.phtml";

    /**
     * This is constructor
     * It is set up template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(self::HISTORY_TEMPLATE);
    }

    /**
     * Retrives ticket
     * @return AW_Helpdeskultimate_Model_Ticket
     */
    public function getTicket()
    {
        if ($ticketId = $this->getRequest()->getParam('id')) {
            return Mage::getModel('helpdeskultimate/ticket')->load($ticketId);
        }
        return false;
    }

    /**
     * Returns tab label
     * @return String
     */
    public function getTabLabel()
    {
        return Mage::helper('helpdeskultimate')->__('Tickets History');
    }

    /**
     * Returns tab title
     * @return String
     */
    public function getTabTitle()
    {
        return Mage::helper('helpdeskultimate')->__('Tickets History');
    }

    /**
     * Check if tab can be displayed
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check if tab is hidden
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Render block HTML
     * @return String
     */
    protected function _toHtml()
    {
        $grid = $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_edit_tab_history_grid');
        $grid->setTicket($this->getTicket());
        $customerEmail = $grid->getTicket()->getCustomerEmail();
        $grid->setCustomerEmailFilter($customerEmail);

        $accordion = $this->getLayout()->createBlock('adminhtml/widget_accordion')->setId('ticketsHistory');

        $accordion->addItem('customers_tickets', array(
            'title' => Mage::helper('helpdeskultimate')->__('Customer tickets history'),
            'content' => "<div class=\"ignore-validate fieldset\">" . $grid->toHtml() . "</div>",
            'open' => true,
        ));
        $this->setChild('accordion', $accordion);

        return parent::_toHtml();
    }


}