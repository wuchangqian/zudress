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

function getStoreConfig($path)
{
    $coll = Mage::getModel('core/config_data')->getCollection();
    $coll->getSelect()->where('path=?', $path);
    foreach ($coll as $i) {
        return $i->getValue();
    }

}


$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$statusProcessed = AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection::STATUS_PROCESSED;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} ADD `store_id` INT( 4 ) NOT NULL AFTER `uid` ;
ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} ADD INDEX ( `store_id` ) ;
ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} ADD `priority` ENUM( 'urgent', 'asap', 'todo', 'iftime', 'postponed' ) NOT NULL DEFAULT 'todo' AFTER `status`;
ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} ADD INDEX ( `priority` ) ;

ALTER TABLE {$this->getTable('helpdeskultimate/message')} ADD INDEX ( `ticket_id` );  
ALTER TABLE {$this->getTable('helpdeskultimate/message')} ADD CONSTRAINT `FK_MSG_TICKET_ID` FOREIGN KEY (`ticket_id`) REFERENCES {$this->getTable('helpdeskultimate/ticket')} (`id`) ON DELETE CASCADE;

ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} ADD `gateway_id` INT( 5 ) NOT NULL AFTER `uid` ;
ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} ADD INDEX ( `gateway_id` ) ;
ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} ADD `content_type` VARCHAR( 32 ) NOT NULL DEFAULT 'text/plain', ADD `attachment` BLOB NOT NULL , ADD `attachment_name` VARCHAR( 255 ) NOT NULL ;

UPDATE {$this->getTable('helpdeskultimate/popmessage')} SET `status` = '{$statusProcessed}';

ALTER TABLE {$this->getTable('helpdeskultimate/department')} ADD `gateways` TEXT NOT NULL ;

CREATE TABLE IF NOT EXISTS {$this->getTable('helpdeskultimate/gateway')} (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `email` varchar(64) NOT NULL,
  `protocol` enum('pop3','imap') NOT NULL default 'pop3',
  `host` varchar(255) NOT NULL,
  `port` int(4) NOT NULL,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `secure` enum('ssl','tls','none') NOT NULL default 'none',
  `delete_message` tinyint(1) NOT NULL default '0',
  `is_active` tinyint(1) NOT NULL default '1',
  `create_tickets` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS {$this->getTable('helpdeskultimate/proto')} (
  `id` int(11) NOT NULL auto_increment,
  `subject` varchar(255) NOT NULL,
  `from` varchar(255) NOT NULL,
  `to` varchar(255) NOT NULL,
  `gateway_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `content_type` varchar(32) NOT NULL,
  `status` enum('pending','processed','failed') NOT NULL default 'pending',
  `priority` enum('urgent','asap','todo','iftime','postponed') NOT NULL default 'todo',
  `filename` varchar(255) NOT NULL,
  `store_id` tinyint(4) NOT NULL,
  `department_id` int(6) NOT NULL default '0',
  `order_id` varchar(64) NOT NULL,
  `source` varchar(64) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `source` (`source`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS {$this->getTable('helpdeskultimate/ticket_flat')} (
  `item_id` int(11) NOT NULL auto_increment,
  `ticket_id` int(11) unsigned NOT NULL,
  `last_reply` datetime default NULL,
  `last_customer_reply` datetime default NULL,
  `last_department_reply` datetime default NULL,
  `total_replies` int(5) NOT NULL,
  PRIMARY KEY  (`item_id`),
  KEY `last_reply` (`last_reply`,`total_replies`),
  KEY `FK_FLAT_TICKET_ID` (`ticket_id`),
  KEY `last_customer_reply` (`last_customer_reply`,`last_department_reply`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=338 ;

ALTER TABLE {$this->getTable('helpdeskultimate/ticket_flat')}
  ADD CONSTRAINT `FK_FLAT_TICKET_ID` FOREIGN KEY (`ticket_id`) REFERENCES {$this->getTable('helpdeskultimate/ticket')} (`id`) ON DELETE CASCADE;
");
$installer->endSetup();


$data1 = array(
    'title' => 'General gateway',
    'email' => getStoreConfig('helpdeskultimate/imap/email'),
    'protocol' => getStoreConfig('helpdeskultimate/imap/type'),
    'host' => getStoreConfig('helpdeskultimate/imap/server'),
    'port' => getStoreConfig('helpdeskultimate/imap/port'),
    'login' => getStoreConfig('helpdeskultimate/imap/login'),
    'password' => getStoreConfig('helpdeskultimate/imap/password'),
    'secure' => getStoreConfig('helpdeskultimate/imap/ssl'),
    'delete_message' => 0,
    'is_active' => 1,
    'create_tickets' => 1
);

// Convert gateway
$newGw = Mage::getModel('helpdeskultimate/gateway');
if ($data1['email']) {
    $newGw->setData($data1)->save();
}

$data2 = array(
    'name' => getStoreConfig('helpdeskultimate/generaldep/name'),
    'contact' => getStoreConfig('helpdeskultimate/generaldep/contact'),
    'email' => getStoreConfig('helpdeskultimate/generaldep/contact'),
    'notify' => getStoreConfig('helpdeskultimate/generaldep/notify'),
    'sender' => getStoreConfig('helpdeskultimate/generaldep/sender'),
    'to_admin_new_email' => 'helpdeskultimate_to_admin_new_email',
    'to_admin_reply_email' => 'helpdeskultimate_to_admin_reply_email',
    'to_admin_reassign_email' => 'helpdeskultimate_to_admin_reassign_email',
    'to_customer_reply_email' => 'helpdeskultimate_to_customer_reply_email',
    'to_customer_new_email' => 'helpdeskultimate_to_customer_new_email',
    'new_from_admin_to_customer' => 'helpdeskultimate_new_from_admin_to_customer',
    'enabled' => 1,
    'primary_store_id' => '',
    'gateways' => ''
);
// Convert general support department
$newDep = Mage::getModel('helpdeskultimate/department');
if ($data2['email']) {
    $newDep->setData($data2)->save();
}


$generalId = (int)$newDep->getId();

// Start converting tickets now

foreach (Mage::getModel('helpdeskultimate/ticket')->getCollection() as $ticket) {
    if (!$ticket->getDepartmentId()) {
        $ticket->setDepartmentId($generalId);
    }
    $ticket->save();
}

$path = Mage::getBaseDir('media') . DS . 'helpdeskultimate';

if (file_exists($path) || (!file_exists($path) && @mkdir($path))) {
    @file_put_contents($path . DS . '.htaccess', 'Deny from all');
}





