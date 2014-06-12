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

class AW_Helpdeskultimate_Model_Source_Ticket_Priority extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const URGENT = 'urgent';
    const ASAP = 'asap';
    const TODO = 'todo';
    const IF_TIME = 'iftime';
    const POSTPONED = 'postponed';

    public function getOptionArray()
    {
        return array(
            self::URGENT => Mage::helper('helpdeskultimate')->__('Urgent'),
            self::ASAP => Mage::helper('helpdeskultimate')->__('ASAP'),
            self::TODO => Mage::helper('helpdeskultimate')->__('To do'),
            self::IF_TIME => Mage::helper('helpdeskultimate')->__('If time'),
            self::POSTPONED => Mage::helper('helpdeskultimate')->__('Postponed')
        );
    }

    public function getAllOptions()
    {
        $arr = array();
        foreach ($this->getOptionArray() as $k => $v) {
            $arr[] = array('label' => $v, 'value' => $k);
        }
        return $arr;
    }

    /**
     * Returns option text by key
     * @param object $id
     * @return
     */
    public function getOption($id)
    {
        $arr = self::getOptionArray();
        return @$arr[$id];
    }
}
