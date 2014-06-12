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

DROP TABLE IF EXISTS `{$this->getTable('aw_hdu_status')}`;

CREATE TABLE IF NOT EXISTS `{$this->getTable('aw_hdu_status')}`(
   `status_id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT ,
   `store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' ,
   `label` VARCHAR(255) NOT NULL ,
   `ordering` INT(12) NOT NULL,
   PRIMARY KEY (`status_id`),
   KEY `FK_HDU_INT_STORE_ID` (`store_id`),
   CONSTRAINT `FK_HDU_INT_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='aheadWorks Help Desk Ultimate Custom statuses';

ALTER TABLE `{$this->getTable('aw_hdu_status')}` AUTO_INCREMENT = 10;

");

$installer->endSetup();






