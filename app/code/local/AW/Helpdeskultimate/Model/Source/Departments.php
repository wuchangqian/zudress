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


class AW_Helpdeskultimate_Model_Source_Departments extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrive all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        // Return users

        /*	$_options = array(
              array('value' => 0, 'label' => '--- General department ---'),
          );*/
        $_options = array();
        try {
            $collection = Mage::getModel('helpdeskultimate/department')
                ->getCollection()
                ->setActiveFilter()
                ->orderByName()
                ->load();
            if ($collection) {
                foreach ($collection as $customer) {
                    array_push(
                        $_options,
                        array(
                            'value' => $customer->getId(),
                            'label' => $customer->getName() . " &lt;{$customer->getEmail()}&gt;"
                        )
                    );
                }
            }
        } catch (Exception $e) {
            // Single department mode
        }
        return $_options;
    }

    public function getFlatOptions()
    {
        // Return departments as array

        $_options = array(
            //0 => 'General department'
        );

        try {
            $collection = Mage::getModel('helpdeskultimate/department')->getCollection()
                ->setActiveFilter()
                ->orderByName()
                ->load();
            if ($collection) {
                foreach ($collection as $dep) {
                    $_options[$dep->getId()] = $dep->getName();
                }
            }
        } catch (Exception $e) {
            // Single department mode
        }
        return $_options;
    }

    public function toOptionArray()
    {
        $_options = array();
        $collection = Mage::getModel('helpdeskultimate/department')
            ->getCollection()
            ->setActiveFilter()
            ->orderByName();
        foreach ($collection as $item) {
            $_options[] = array('value' => $item->getId(), 'label' => $item->getName());
        }
        return $_options;
    }
}
