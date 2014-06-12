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
ALTER TABLE {$this->getTable('helpdeskultimate/department')} ADD `new_from_admin_to_customer` VARCHAR( 255 ) NOT NULL DEFAULT 'helpdeskultimate_new_from_admin_to_customer' AFTER `to_customer_new_email`;

ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} CHANGE `body` `body` MEDIUMTEXT NOT NULL; 
ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} ADD `hash` VARCHAR( 32 ) NOT NULL ;
ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} ADD INDEX ( `hash` ) ;

ALTER TABLE {$this->getTable('helpdeskultimate/message')} 
    ADD `content_type` VARCHAR( 64 ) NOT NULL DEFAULT 'text/plain',
    ADD `from_email` VARCHAR( 255 ) NOT NULL DEFAULT '0',
    ADD `department_id` INT( 11 ) NOT NULL ,
    ADD `customer_id` INT( 11 ) NOT NULL ;

ALTER TABLE {$this->getTable('helpdeskultimate/message')} ADD INDEX ( `from_email` , `department_id` , `customer_id` ) ;

CREATE TABLE IF NOT EXISTS {$this->getTable('helpdeskultimate/template')} (
    `id` int(11) NOT NULL auto_increment,
    `enabled` int(1) NOT NULL default '1',
    `name` varchar(255) NOT NULL,
    `content` text NOT NULL,
    `department_id` int(11) NOT NULL,
    PRIMARY KEY  (`id`),
    KEY `department_id` (`department_id`),
    KEY `enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} ADD `created_by` ENUM( 'customer', 'admin' ) NOT NULL DEFAULT 'customer' AFTER `department_id` ;

ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} ADD INDEX ( `created_by` ) ;

ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} 
    ADD `order_incremental_id` VARCHAR( 128 ) NOT NULL ,
    ADD `content_type` VARCHAR( 64 ) NOT NULL DEFAULT 'text/plain';

");

// Check now if CF integration is in use and restore original recipient
if (
    Mage::getStoreConfig(AW_Helpdeskultimate_Helper_Config::XML_PATH_SELF_EMAIL) == Mage::getStoreConfig(AW_Helpdeskultimate_Helper_Config::XML_PATH_CF_EMAIL)
) {
    // Restore 
    if (Mage::getStoreConfig(AW_Helpdeskultimate_Helper_Config::XML_PATH_STORED_CF_EMAIL)) {
        Mage::getModel('adminhtml/config_data')
                ->setSection('contacts')
                ->setWebsite(0)
                ->setStore(0)
                ->setGroups(array(
                                 'email' => array(
                                     'fields' => array(
                                         'recipient_email' => array('value' => Mage::getStoreConfig(AW_Helpdeskultimate_Helper_Config::XML_PATH_STORED_CF_EMAIL))
                                     )
                                 )
                            ))
                ->save();
    }
}

$installer->endSetup();
