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
 * @package    AW_Followupemail
 * @version    3.5.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Followupemail_Model_Source_Rule_Cross
{
    const MAGENTO_CROSS = 'magento_cross';
    const MAGENTO_RELATED = 'magento_related';
    const MAGENTO_UPSELLS = 'magento_upsells';
    const AW_WBTAB = 'aw_wbtab';
    const AW_ARP2 = 'aw_arp2';

    public static function toOptionArray()
    {
        $helper = Mage::helper('followupemail');
        $options = array(
            array('value' => self::MAGENTO_CROSS,   'label' => $helper->__('Magento Cross-sell products')),
            array('value' => self::MAGENTO_RELATED, 'label' => $helper->__('Magento Related products')),
            array('value' => self::MAGENTO_UPSELLS, 'label' => $helper->__('Magento Upsell products')),
            array('value' => self::AW_WBTAB,        'label' => $helper->__('AW Who bought this also bought')),
            array('value' => self::AW_ARP2,         'label' => $helper->__('AW Autorelated products 2')),
        );
        return $options;
    }

}