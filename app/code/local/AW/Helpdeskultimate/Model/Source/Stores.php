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


class AW_Helpdeskultimate_Model_Source_Stores extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const NO_STORE = 0;

    const NO_STORE_LABEL = '--- No store ---';

    /**
     * Retrive all attribute options
     *
     * @return array
     */

    public function getAllOptions()
    {
        // Return users

        $currentDepartment = Mage::registry('department');

        $_options = array(
            array('value' => self::NO_STORE, 'label' => self::NO_STORE_LABEL),
        );
        try {
            $collection = Mage::getSingleton('adminhtml/system_store')
                    ->getStoreCollection();

            $arr = array();
            $deps = Mage::getModel('helpdeskultimate/department')->getCollection();
            foreach ($deps as $dep) {
                $arr[] = $dep->getPrimaryStoreId();
            }

            if ($collection) {
                foreach ($collection as $store) {
                    if (
                        !in_array($store->getId(), $arr)
                        || ($currentDepartment->getPrimaryStoreId() == $store->getId())
                    ) {
                        array_push($_options, array('value' => $store->getId(), 'label' => $store->getName() . ""));
                    }
                }
            }
        } catch (Exception $e) {
            // Single department mode
        }
        return $_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }


}
