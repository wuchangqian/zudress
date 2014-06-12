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


class AW_Helpdeskultimate_Model_Mysql4_Department_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('helpdeskultimate/department');
    }

    public function addActiveFilter()
    {
        return $this->setActiveFilter();
    }

    public function setActiveFilter()
    {
        $this->getSelect()
            ->where("enabled!=?", 0);
        return $this;
    }

    public function setVisibilityFilter($store = null)
    {
        if (is_null($store)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        $this->getSelect()->where(
            $this->_getConditionSql('visible_on', array('finset' => $storeId))
            . ' OR '
            . $this->_getConditionSql('visible_on', array('finset' => '0'))
        );
        return $this;
    }

    public function setContactFilter($email)
    {
        $this->getSelect()
            ->where("contact=?", $email);
        return $this;
    }

    public function setPrimaryStoreIdFilter($id)
    {
        $this->getSelect()
            ->where("primary_store_id=?", $id);
        return $this;
    }

    public function getAffectedStores()
    {
        $this->getSelect()
            ->distinct()
            ->from(array('p' => $this->getTable('helpdeskultimate/department')), 'store_id');
        return $this;
    }

    public function orderByName($direction = self::SORT_ORDER_ASC)
    {
        $this->addOrder('name', $direction);
        return $this;
    }

    public function orderByDisplayOrder($direction = self::SORT_ORDER_ASC)
    {
        $this->addOrder('display_order', $direction);
        return $this;
    }
}
