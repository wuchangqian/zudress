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
    CREATE TABLE IF NOT EXISTS {$this->getTable('helpdeskultimate/rpattern')} (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `name` TINYTEXT NOT NULL ,
        `is_active` TINYINT NOT NULL ,
        `scope` TEXT NOT NULL ,
        `pattern` TEXT NOT NULL
    ) ENGINE = InnoDB ;
    
    INSERT IGNORE INTO `{$this->getTable('helpdeskultimate/rpattern')}` (`id`, `name`, `is_active`, `scope`, `pattern`) VALUES
        (1, 'Auto-Submitted header', 1, '" . AW_Helpdeskultimate_Model_Source_Rejectedemails_Scope::HEADERS . "', '/(?i:auto-submitted: )(?i:(?!no)).*/m'),
        (2, 'Having X-Spam-Flag header set to YES', 1, '" . AW_Helpdeskultimate_Model_Source_Rejectedemails_Scope::HEADERS . "', '/x-spam-flag: yes/mi'),
        (3, 'X-Spam header', 1, '" . AW_Helpdeskultimate_Model_Source_Rejectedemails_Scope::HEADERS . "', '/^x-spam: (?!not detected).*$/mi')
    ;
");
$this->getConnection()
        ->addColumn($this->getTable('helpdeskultimate/popmessage'), 'rej_pid', 'INT UNSIGNED NOT NULL');
$installer->endSetup();
