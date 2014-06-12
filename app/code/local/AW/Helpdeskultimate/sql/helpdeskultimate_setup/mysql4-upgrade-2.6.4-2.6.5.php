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

$installer->startSetup();
$installer->run("
        ALTER TABLE `{$this->getTable('helpdeskultimate/popmessage')}` CHANGE `attachment` `attachment` MEDIUMBLOB;
        ALTER TABLE `{$this->getTable('helpdeskultimate/department')}` ADD `visibility` INT(1) NOT NULL DEFAULT '1';
        ALTER TABLE `{$this->getTable('helpdeskultimate/popmessage')}` DROP INDEX `uid` , ADD UNIQUE `uid` ( `uid` , `gateway_id` );
        ALTER TABLE `{$this->getTable('helpdeskultimate/status')}` ADD `status_type` VARCHAR(100) NOT NULL DEFAULT 'custom';
        ALTER TABLE `{$this->getTable('helpdeskultimate/status')}` AUTO_INCREMENT = 10;
        ");
$installer->endSetup();
