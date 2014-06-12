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

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('helpdeskultimate_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Ticket Information'));
    }

    protected function _beforeToHtml()
    {
        $tabThreadContent = '';
        if ($this->getRequest()->getParam('id')) {
            $ticketId = $this->getRequest()->getParam('id');
            $messagesCollection = Mage::getModel('helpdeskultimate/ticket')
                ->load($ticketId)
                ->getMessages()
                ->orderBy('created_time DESC')
                ->load();

            $tabThreadContent .= $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_edit_tab_thread')
                ->setCollection($messagesCollection)
                ->toHtml();
        }

        $this->addTab('form_section', array(
            'label' => $this->__('Ticket Information'),
            'title' => $this->__('Ticket Information'),
            'content' => $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_edit_tab_operations')
                            ->toHtml()
                       . $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_edit_tab_form')
                            ->toHtml()
                       . $tabThreadContent
        ));

        if ($this->getRequest()->getParam('id')) {
            /* Draw additional information if editiong ticket */
            $this->addTab('additional_section', array(
                'label'   => $this->__('Additional Information'),
                'title'   => $this->__('Additional Information'),
                'content' => $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_edit_tab_additional')
                    ->toHtml()
            ));


            $this->addTab('status_section', array(
                'label'   => $this->__('Status History'),
                'title'   => $this->__('Status History'),
                'content' => $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_edit_tab_status')
                    ->toHtml()
            ));
        }
        $this->addTab('notes_section', array(
            'label'   => $this->__('Notes'),
            'title'   => $this->__('Notes'),
            'content' => $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_edit_tab_notes')
                ->toHtml()
        ));

        if ($this->getRequest()->getParam('id')) {
            $this->addTab('history_section', array(
                 'label'   => $this->__('Tickets History'),
                 'title'   => $this->__('Tickets History'),
                 'content' => $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_edit_tab_history')
                     ->toHtml()
            ));
        }
        return parent::_beforeToHtml();
    }
}
