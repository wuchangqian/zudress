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

class AW_Helpdeskultimate_Model_Search_Helpdeskultimate extends Varien_Object
{

    public function load()
    {
        $arr = array();

        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }

        $query = addslashes($this->getQuery());
        $collection = Mage::getModel('helpdeskultimate/ticket')->getCollection();
        $tableMessages = Mage::getResourceModel('helpdeskultimate/message')->getTable('helpdeskultimate/message');
        $collection->getSelect()
                ->columns('GROUP_CONCAT(m.content SEPARATOR " ") AS message_content')
                ->joinLeft(
                    array('m' => $tableMessages),
                    'm.ticket_id=main_table.id',
                    array('message_id' => 'm.id')
                )
                ->group('uid')
                ->having(
                    "main_table.title LIKE '%$query%'
                    OR message_content LIKE '%$query%'
                    OR main_table.content LIKE '%$query%'
                    OR main_table.customer_email LIKE '%$query%'
                    OR main_table.customer_name LIKE '%$query%'"
                );

        foreach ($collection as $ticket) {
            $arr[] = array(
                'id' => 'ticket/1/' . $ticket->getId(),
                'type' => 'Help Desk Ticket',
                'name' => Mage::helper('helpdeskultimate')
                    ->__('Ticket #%s', $ticket->getUid()) . ' ' . $ticket->getTitle(),
                'description' => $ticket->getCustomerName() . ' ' . $ticket->getCustomerEmail(),
                'form_panel_title' => Mage::helper('helpdeskultimate')
                    ->__(
                        'Ticket #%s (%s)',
                        $ticket->getUid(),
                        $ticket->getCustomerName() . ' ' . $ticket->getCustomerEmail()
                    )
                    . ' ' . $ticket->getTitle(),
                'url' => Mage::getSingleton('adminhtml/url')
                    ->getUrl('helpdeskultimate_admin/ticket/edit', array('id' => $ticket->getId())),
            );
        }

        $this->setResults($arr);
        return $this;
    }

}

