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


class AW_Helpdeskultimate_Model_Department_Permissions extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdeskultimate/department_permissions');
    }

    public function loadByRoleId($roleId)
    {
        return $this->load($roleId, 'role_id');
    }

    /**
     * Unserialize value
     * @return
     */
    protected function _afterLoad()
    {
        $value = $this->getData('value');
        if (!empty($value)) {
            $value = explode(',', $value);
        } else {
            $value = array();
        }
        $this->setData('value', $value);
        return parent::_afterLoad();
    }

    /**
     * Serialize value
     * @return
     */
    protected function _beforeSave()
    {
        if (is_array($this->getData('value'))) {
            $values = implode(',', $this->getData('value'));
            $this->setData('value', $values);
        } elseif (is_null($this->getData('value'))) {
            $this->setData('value', '');
        }
        return parent::_beforeSave();
    }

}