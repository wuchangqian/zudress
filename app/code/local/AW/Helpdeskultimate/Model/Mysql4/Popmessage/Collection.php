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
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 * @version    1.1
 */

class AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    private $_isStatusFilterSetted = false;
    private $_isRejectedPatternNamesJoined = false;

    const STATUS_PROCESSED = 1;
    const STATUS_UNPROCESSED = 2;
    const STATUS_REJECTED = 3;

    public function _construct()
    {
        parent::_construct();
        $this->_init('helpdeskultimate/popmessage');
    }

    public function joinPatternNames()
    {
        if (!$this->_isRejectedPatternNamesJoined) {
            $this->getSelect()->joinLeft(
                array('patterns' => $this->getTable('helpdeskultimate/rpattern')),
                'main_table.rej_pid = patterns.id',
                array('pattern_name' => 'patterns.name')
            );
            $this->_isRejectedPatternNamesJoined = true;
        }
        return $this;
    }

    public function addUnprocessedFilter()
    {
        return $this->addStatusFilter(self::STATUS_UNPROCESSED);
    }

    /**
     * Excludes messages with specified UIDs from collection
     *
     * @param object $uids
     * @return AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection
     */
    public function addExcludeUIDFilter(Array $uids)
    {
        if (!sizeof($uids)) {
            return $this;
        }
        $uidsPart = "'" . implode("', '", $uids) . "'";
        $this->getSelect()->where("uid NOT IN $uidsPart");
        return $this;
    }

    public function addHashFilter($hash)
    {
        $this->getSelect()->where('hash=?', $hash);
        return $this;
    }

    /**
     * Adds filter by gateway
     * @param object $id
     * @return
     */
    public function addGatewayIdFilter($id)
    {
        $this->getSelect()->where('gateway_id=?', $id);
        $this->getSelect()->orwhere('gateway_id=0');
        return $this;
    }

    /**
     * Adds rejected filter
     * @return AW_Helpdeskultimate_Model_Mysql4_Proto_Collection
     */
    public function addRejectedFilter()
    {
        return $this->addStatusFilter(self::STATUS_REJECTED);
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
