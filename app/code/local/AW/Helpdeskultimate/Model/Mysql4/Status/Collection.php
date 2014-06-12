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
 * Status Resource collection
 */
class AW_Helpdeskultimate_Model_Mysql4_Status_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Class constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('helpdeskultimate/status');
    }

    /**
     * Adds filter by store id
     * @param int $id
     * @return AW_Helpdeskultimate_Model_Mysql4_Status_Collection
     */
    public function addPublicStoreFilter()
    {
        $this->getSelect()->where('store_id=0');
        return $this;
    }

    /**
     * Adds filter by store id
     * @param int $id
     * @return AW_Helpdeskultimate_Model_Mysql4_Status_Collection
     */
    public function addStoreFilter($id)
    {
        if ($id && is_numeric($id) || ($id === 0)) {
            $this->getSelect()->where('store_id=' . $id);
        }
        return $this;
    }

    public function load($printQuery = false, $logQuery = false)
    {
        $this->setOrder('ordering', self::SORT_ORDER_ASC);
        return parent::load($printQuery . $logQuery);
    }
}
