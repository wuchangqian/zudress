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
 * Resource model of Status
 */
class AW_Helpdeskultimate_Model_Mysql4_Status extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdeskultimate/status', 'status_id');
    }

    public function setOrdering($statusId, $ordering)
    {
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array('ordering' => $ordering),
            "status_id = '{$statusId}'"
        );
    }

    public function getUsedCount($statusId)
    {
        $tickets = Mage::getModel('helpdeskultimate/ticket')->getCollection();
        $tickets->getSelect()->where('status = ?', $statusId);
        return $tickets->getSize();
    }

    public function statusExist($value)
    {
        $status = Mage::getModel('helpdeskultimate/status')->getCollection();
        $status->getSelect()
            ->where('label = ?', $value)
            ->where("status_type != 'admin'");
        return $status->getSize();
    }

}
