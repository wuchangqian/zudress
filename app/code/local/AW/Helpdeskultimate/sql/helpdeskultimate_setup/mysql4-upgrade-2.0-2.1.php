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


ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} ENGINE = InnoDB;


ALTER TABLE {$this->getTable('helpdeskultimate/ticket')}  DEFAULT CHARACTER SET utf8;
ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')}  DEFAULT CHARACTER SET utf8;
ALTER TABLE {$this->getTable('helpdeskultimate/message')}  DEFAULT CHARACTER SET utf8;
ALTER TABLE {$this->getTable('helpdeskultimate/department')}  DEFAULT CHARACTER SET utf8;


ALTER TABLE {$this->getTable('helpdeskultimate/department')} CHANGE `name` `name` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/department')} CHANGE `contact` `contact` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;
ALTER TABLE {$this->getTable('helpdeskultimate/department')} CHANGE `email` `email` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;
ALTER TABLE {$this->getTable('helpdeskultimate/department')} CHANGE `sender` `sender` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;
ALTER TABLE {$this->getTable('helpdeskultimate/department')} CHANGE `to_admin_new_email` `to_admin_new_email` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;
ALTER TABLE {$this->getTable('helpdeskultimate/department')} CHANGE `to_admin_reply_email` `to_admin_reply_email` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;
ALTER TABLE {$this->getTable('helpdeskultimate/department')} CHANGE `to_customer_reply_email` `to_customer_reply_email` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;
ALTER TABLE {$this->getTable('helpdeskultimate/department')} CHANGE `to_customer_new_email` `to_customer_new_email` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;


ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} CHANGE `uid` `uid` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} CHANGE `from` `from` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} CHANGE `to` `to` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} CHANGE `subject` `subject` TEXT CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} CHANGE `body` `body` TEXT CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/popmessage')} CHANGE `type` `type` VARCHAR( 64 ) CHARACTER SET utf8 NOT NULL;  


ALTER TABLE {$this->getTable('helpdeskultimate/message')} CHANGE `title` `title` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/message')} CHANGE `filename` `filename` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/message')} CHANGE `content` `content` TEXT CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/message')} CHANGE `author_name` `author_name` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;  


ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} CHANGE `uid` `uid` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} CHANGE `title` `title` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} CHANGE `filename` `filename` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} CHANGE `content` `content` TEXT CHARACTER SET utf8 NOT NULL;  
ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} CHANGE `customer_email` `customer_email` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;
ALTER TABLE {$this->getTable('helpdeskultimate/ticket')} CHANGE `customer_name` `customer_name` VARCHAR( 255 ) CHARACTER SET utf8 NOT NULL;

");

$installer->endSetup();



?>
