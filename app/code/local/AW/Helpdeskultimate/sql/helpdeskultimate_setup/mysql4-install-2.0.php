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

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('helpdeskultimate/department')} (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `notify` int(1) NOT NULL,
  `sender` varchar(255) NOT NULL,
  `to_admin_new_email` varchar(255) NOT NULL,
  `to_admin_reply_email` varchar(255) NOT NULL,
  `to_customer_reply_email` varchar(255) NOT NULL,
  `to_customer_new_email` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `email` (`contact`),
  KEY `notify` (`notify`,`sender`),
  KEY `enabled` (`enabled`),
  KEY `email_2` (`email`)
) ENGINE=MyISAM  ;



CREATE TABLE IF NOT EXISTS {$this->getTable('helpdeskultimate/popmessage')} (
  `id` int(11) NOT NULL auto_increment,
  `uid` varchar(255),
  `from` varchar(255) NOT NULL,
  `to` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  `type` varchar(64) NOT NULL default 'text',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `from` (`from`),
  KEY `to` (`to`),
  KEY `date` (`date`),
  KEY `status` (`status`),
  KEY `type` (`type`)
) ENGINE=MyISAM;



CREATE TABLE IF NOT EXISTS {$this->getTable('helpdeskultimate/message')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ticket_id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `is_reply` smallint(6) NOT NULL default '0',
  `created_time` datetime default NULL,
  `author_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS {$this->getTable('helpdeskultimate/ticket')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `uid` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `status` smallint(6) NOT NULL default '1',
  `created_time` datetime default NULL,
  `customer_email` varchar(255) default NULL,
  `customer_name` varchar(255) default NULL,
  `customer_id` int(11) default NULL,
  `department_id` int(11) NOT NULL default '0',
  `is_virtual` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `is_virtual` (`is_virtual`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB;
");

$installer->endSetup();

// Test if old helpdesk is active
try {
    if (!Mage::getModel('helpdesk/helpdesk')) throw new Exception('No Help Desk found');
    $old_tickets = Mage::getModel('helpdesk/helpdesk')->getCollection()->load();
    foreach ($old_tickets as $item) {
        $ticket = Mage::getModel('helpdeskultimate/ticket');

        $ticket
                ->setTitle($item->getTitle())
                ->setFilename($item->getFilename())
                ->setContent($item->getContent())
                ->setStatus($item->getStatus())
                ->setCreatedTime($item->getCreatedTime())
                ->setCustomerEmail($item->getCustomerEmail())
                ->setCustomerName($item->getCustomerName())
                ->setCustomerId($item->getCustomerId())
                ->save();

        if ($item->getFilename()) {
            // If file exists, we should copy that to new location

            @mkdir($ticket->getFolderName());

            @copy(
                $item->getFolderName() . $item->getFilename(),
                $ticket->getFolderName() . $item->getFilename()
            );
        }

        // Now messages for the ticket
        // Unlinked messages will not be copied

        $old_messages = Mage::getModel('helpdesk/helpdesk_messages')
                ->getCollection()
                ->addThreadFilter(
            $item->getHelpdeskId()
        );
        foreach ($old_messages as $old_message) {
            $message = Mage::getModel('helpdeskultimate/message');
            //	var_dump($old_message->getIsReply());
            $message
                    ->setTicketId($ticket->getId())
                    ->setTitle($old_message->getTitle())
                    ->setFilename($old_message->getFilename())
                    ->setContent($old_message->getContent())
                    ->setIsReply($old_message->getIsReply())
                    ->setCreatedTime($old_message->getCreatedTime())
                    ->setAuthorName(
                $old_message->getIsReply() ?
                        'Support' :
                        $ticket->getCustomerName()
            )
                    ->save();

            if ($old_message->getFilename()) {
                // If file exists, we should copy that to new location

                @mkdir($message->getFolderName());

                @copy(
                    $old_message->getFolderName() . $old_message->getFilename(),
                    $message->getFolderName() . $old_message->getFilename()
                );
            }
        }
    }


} catch (Exception $e) {
    // No helpdesk found
}



?>
