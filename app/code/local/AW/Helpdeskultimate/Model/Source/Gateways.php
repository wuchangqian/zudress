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


class AW_Helpdeskultimate_Model_Source_Gateways extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const ALL_GATEWAYS = 0;

    const ALL_GATEWAYS_LABEL = 'All';

    /**
     * Retrive all attribute options
     *
     * @return array
     */

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    /**
     * Converts to options
     * @return array
     */
    public function toOptionArray()
    {
        $coll = Mage::getModel('helpdeskultimate/gateway')->getCollection()->addActiveFilter();

        $data = array(
            array(
                'value' => self::ALL_GATEWAYS,
                'label' => Mage::helper('helpdeskultimate')->__(self::ALL_GATEWAYS_LABEL)
            )
        );
        foreach ($coll as $gw) {
            $data[] = array('value' => $gw->getId(), 'label' => $gw->getTitle());
        }
        return $data;
    }


}
