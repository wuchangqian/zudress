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


class AW_Helpdeskultimate_Model_Source_Transport extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const POP3 = 'pop3';
    const IMAP = 'imap';

    const POP3_LABEL = 'POP3';
    const IMAP_LABEL = 'IMAP';
    /**
     * Retrive all attribute options
     *
     * @return array
     */

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    public function toOptionArray()
    {
        return array(
            array('value' => self::POP3, 'label' => Mage::helper('helpdeskultimate')->__(self::POP3_LABEL)),
            array('value' => self::IMAP, 'label' => Mage::helper('helpdeskultimate')->__(self::IMAP_LABEL))
        );
    }


}
