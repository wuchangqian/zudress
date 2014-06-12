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

class AW_Helpdeskultimate_Model_Mysql4_Ticket extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdeskultimate/ticket', 'id');
    }

    /**
     * Merge also data from flat table
     * @param Mage_Core_Model_Abstract $object
     * @param object                   $value
     * @param object                   $field [optional]
     * @return
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (is_null($field)) {
            $field = $this->getIdFieldName();
        }
        $read = $this->_getReadAdapter();
        if ($read && !is_null($value)) {
            $select = $this->_getLoadSelect($field, $value, $object)->joinLeft(
                array('f' => Mage::getResourceModel('helpdeskultimate/ticket_flat')->getMainTable()),
                'f.ticket_id=' . $this->getMainTable() . '.id'
            );

            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }
        $this->_afterLoad($object);
        return $this;
    }
}
