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


/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Helpdeskultimate
 * @copyright  Copyright (c) 2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @var $this Mage_Core_Model_Resource_Setup
 */

$installer = $this->startSetup();

try {
    $installer->getConnection()
                    ->addColumn(
                        $this->getTable('helpdeskultimate/department'),
                        'display_order',
                        'INT unsigned NOT NULL AFTER visible_on'
                    );

    $installer->run("
        CREATE TABLE IF NOT EXISTS {$this->getTable('helpdeskultimate/department_permissions')} (
            `id` INT( 11 ) NOT NULL auto_increment ,
            `role_id` INT(11) NOT NULL ,
            `value` TEXT NOT NULL ,
            PRIMARY KEY ( `id` )
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;
    ");

    $departments = $collection = Mage::getModel('helpdeskultimate/department')->getCollection()->getAllIds();
    $roles = Mage::getModel('admin/role')->getCollection()->setRolesFilter()->getAllIds();
    foreach ($roles as $roleId) {
        $departmentPermission = Mage::getModel('helpdeskultimate/department_permissions');
        $departmentPermission->setData(array(
            'role_id' => $roleId,
            'value' => $departments
        ));
        $departmentPermission->save();
    }

} catch(Exception $e) {
    Mage::log($e->getMessage());
}
$installer->endSetup();
