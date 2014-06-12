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

class AW_Helpdeskultimate_Model_Mysql4_Proto_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    private $_isStatusFilterSetted = false;

    public function _construct()
    {
        parent::_construct();
        $this->_init('helpdeskultimate/proto');
    }

    /**
     * Adds pending filter
     * @return AW_Helpdeskultimate_Model_Mysql4_Proto_Collection
     */
    public function addPendingFilter()
    {
        return $this->addStatusFilter(AW_Helpdeskultimate_Model_Proto::STATUS_PENDING);
    }

    /**
     * Filters collection by status
     * @param String $status
     * @return AW_Helpdeskultimate_Model_Mysql4_Proto_Collection
     */
    public function addStatusFilter($status)
    {
        if (!$this->_isStatusFilterSetted) {
            $this->getSelect()->where('status = ?', $status);
            $this->_isStatusFilterSetted = true;
        }
        return $this;
    }
}
