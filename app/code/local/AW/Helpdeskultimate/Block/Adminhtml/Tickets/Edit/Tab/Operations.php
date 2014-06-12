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

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tab_Operations extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'helpdeskultimate_form',
            array('legend' => Mage::helper('helpdeskultimate')->__('Ticket Options'))
        );

        $fieldset->addField('id', 'hidden', array(
            'required' => false,
            'name' => 'id',
        ));

        $statuses = Mage::getModel('helpdeskultimate/status')->getAllOptions();
        if (strstr($this->getRequest()->getOriginalPathInfo(), 'new')) {
            $adminStatusId = Mage::getModel('helpdeskultimate/status')->load('admin', 'status_type')->getId();
            $adminStatusId = ($adminStatusId) ? $adminStatusId : AW_Helpdeskultimate_Model_Status::STATUS_WAITING;
            $trueStatuses = array();
            foreach ($statuses as $key => $stat) {
                if ($stat['value'] == $adminStatusId) {
                    $trueStatuses[0] = $stat;
                    unset($statuses[$key]);
                }
            }
            foreach ($statuses as $stat) {
                $trueStatuses[] = $stat;
            }
        } else {
            $trueStatuses = $statuses;
        }
        $fieldset->addField('status_id', 'select', array(
            'label' => Mage::helper('helpdeskultimate')->__('Set status to'),
            'name' => 'status_id',
            'values' => $trueStatuses,
        ));

        $fieldset->addField('saved_priority', 'select', array(
            'label' => Mage::helper('helpdeskultimate')->__('Priority'),
            'name' => 'saved_priority',
            'values' => Mage::getModel('helpdeskultimate/source_ticket_priority')->getAllOptions(),
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'select', array(
                'name' => 'store_id',
                'label' => Mage::helper('cms')->__('Store View'),
                'title' => Mage::helper('cms')->__('Store View'),
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
            ));
        }

        $fieldset->addField('lock', 'select', array(
            'label' => Mage::helper('helpdeskultimate')->__('Locked'),
            'name' => 'lock',
            'values' => array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('helpdeskultimate')->__('No'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('helpdeskultimate')->__('Yes'),
                )
            )
        ));
        $_buttonAsHtml = '<button class="scalable" id="assign_button" type="button"><span>'
                       . $this->__('Find customer')
                       . '</span></button>';
        $fieldset->addField('customer_email', 'text', array(
            'label' => Mage::helper('helpdeskultimate')->__('Assign to customer'),
            'name' => 'customer_email',
            'style' => 'width:200px;',
            'required' => true,
            'after_element_html' => $_buttonAsHtml,
        ));

        $ticket = Mage::registry('hdu_ticket_data');
        $customerId = $ticket->getCustomer()->getId();
        $customerName = $ticket->getCustomer()->getName();
        $customerEmail = $ticket->getCustomer()->getEmail();

        if ($ticket->getCustomer()->getId()) {
            AW_Helpdeskultimate_Model_Source_Users::$userName = $ticket->getCustomer()->getName();

            if (Mage::helper('helpdeskultimate')->checkVersion('1.8.0.0')) {
                $values = array($customerId => "{$customerName} <$customerEmail}>");
            } else {
                $values = array($customerId => "{$customerName} &lt;{$customerEmail}&gt;");
            }

        } elseif ($ticket->getCustomerEmail()) {
            AW_Helpdeskultimate_Model_Source_Users::$userName = "";
            $fullName = $ticket->getCustomerName() ? $ticket->getCustomerName() : $ticket->getCustomerEmail();
            if (Mage::helper('helpdeskultimate')->checkVersion('1.8.0.0')) {
                $fullName .= " <" . $ticket->getCustomerEmail() . "> [Unregistered]";
            } else {
                $fullName .= " &lt;" . $ticket->getCustomerEmail() . "&gt; [Unregistered]";
            }

            $values = array(
                $ticket->getCustomerName() . " " . $ticket->getCustomerEmail() => $fullName
            );
        } else {
            $style = 'display:none;';
            $values = array("-1" => "");
        }
        $isAssigned = $ticket->getCustomerEmail() || Mage::registry('hdu_ticket_data')->getCustomer()->getId();

        $style = isset($style) ? $style : "margin-right:10px;";

        $_url = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/customer/edit', array('id' => $customerId));
        $_afterElementHtml = '<div>'
                           . '<a href="' . $_url . '" id="customer_account_link" target="_blank" style="display:none;">'
                           . Mage::helper('helpdeskultimate')->__('View customer')
                           . '</a></div>';
        $_afterElementHtml .= '<div id="no_users_found" style="color:#666; font-style:italic;'
                            . ($isAssigned ? 'display:none;' : '') . '" >';
        $_afterElementHtml .= $this->__('Start typing name or email above, then click "Find customer"');
        $_afterElementHtml .= '</div>';

        $fieldset->addField('customer_id', 'select', array(
            'label' => $this->__(''),
            'name' => 'customer_id',
            'values' => $values,
            'style' => $style,
            'after_element_html' => $_afterElementHtml,
        ));

        $fieldset->addField('department_saved_id', 'select', array(
            'label' => Mage::helper('helpdeskultimate')->__('Assign to department'),
            'name' => 'department_saved_id',
            'values' => Mage::getModel('helpdeskultimate/source_departments')->getAllOptions(),
        ));

        $_afterElementHtml = '<button class="scalable disabled" id="view-order-button" type="button"><span>'
                           . $this->__('View order')
                           . '</span></button>';
        $_customerId = $ticket->getCustomer()->getId();
        $_customerEmail = $ticket->getCustomerEmail();
        $_orderId = $ticket->getOrderId();
        $_isOrderNotBelongToCustomer = !Mage::helper('helpdeskultimate')->isOrderBelongToCustomer(
            $_orderId,
            $_customerId,
            $_customerEmail
        );
        $_afterElementHtml .= "<span id='assign-to-order-note' class='span-note' "
                            . ($_isOrderNotBelongToCustomer?"style='display:block'":"") . ">";
        $_afterElementHtml .= $this->__('Note: Selected order does not belong to current customer');
        $_afterElementHtml .= "</span>";
        if ($ticket->getOrderId() == '0' && strlen($ticket->getOrderIncrementalId()) > 0) {
            $_afterElementHtml .= "<span id='no-exist-order-note' class='span-note'>";
            $_afterElementHtml .= $this->__('Note: Order does not exist');
            $_afterElementHtml .= "</span>";
        }
        $fieldset->addField('order_incremental_id', 'text', array(
             'label' => Mage::helper('helpdeskultimate')->__('Assign to order #'),
             'name' => 'order_incremental_id',
             'style' => 'width:187px;',
             'class' => 'order-disabled',
             'after_element_html' => $_afterElementHtml,
        ));
        $fieldset->addField('order_id', 'hidden', array(
            'name' => 'order_id',
        ));

        $_buttonAsHtml = '<button class="scalable" id="templates_button" type="button"><span>'
                       . $this->__('Paste template') . '</span></button>';
        $fieldset->addField('quick_template', 'select', array(
             'label' => Mage::helper('helpdeskultimate')->__('Use template'),
             'name' => 'quick_template',
             'style' => 'width:218px;',
             'class' => 'order-disabled',
             'values' => Mage::getModel('helpdeskultimate/source_templates')->getAllOptions(),
             'after_element_html' => $_buttonAsHtml,
        ));

        if (Mage::getSingleton('adminhtml/session')->getFormData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFormData());
        } elseif (Mage::registry('hdu_ticket_data')) {
            if (!Mage::registry('hdu_ticket_data')->getTitle()) {
                Mage::registry('hdu_ticket_data')->setTitle(
                    Mage::getSingleton('helpdeskultimate/ticket')->load($this->getRequest()->getParam('id'))->getTitle()
                );
            }

            if (!Mage::registry('hdu_ticket_data')->getPriority()) {
                Mage::registry('hdu_ticket_data')->setPriority('todo');
            }

            $status = Mage::getSingleton('helpdeskultimate/ticket')->load($this->getRequest()->getParam('id'))
                    ->getStatus();
            if ($status == AW_Helpdeskultimate_Model_Status::STATUS_OPEN)
                $status = AW_Helpdeskultimate_Model_Status::STATUS_WAITING;

            Mage::registry('hdu_ticket_data')->setStatusId($status);


            if (Mage::registry('hdu_ticket_data')) {
                if (
                    Mage::registry('hdu_ticket_data')->getData('order_id')
                    && !Mage::registry('hdu_ticket_data')->getData('order_incremental_id')
                ) {
                    Mage::registry('hdu_ticket_data')->setData(
                        'order_incremental_id',
                        $ticket->getOrder()->getIncrementId()
                    );
                }
            }
            if ($id = $this->getRequest()->getParam('order_id'))
                Mage::registry('hdu_ticket_data')->setData('order_id', $id);


            $form->setValues(
                array_merge(
                    Mage::registry('hdu_ticket_data')->getData(),
                    array(
                         'id' => $this->getRequest()->getParam('id'),
                         'lock' => intval(!!Mage::registry('hdu_ticket_data')->getLockedBy()),
                         'customer_suggest' => Mage::registry('hdu_ticket_data')->getCustomer()->getName(),
                         'priority' => Mage::registry('hdu_ticket_data')->getData('priority')
                                 ? Mage::registry('hdu_ticket_data')->getData('priority') : 'todo'
                    )
                )
            );

        }
        return parent::_prepareForm();
    }
}
