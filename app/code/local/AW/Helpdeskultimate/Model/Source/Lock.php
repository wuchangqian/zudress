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

class AW_Helpdeskultimate_Model_Source_Lock extends AW_Helpdeskultimate_Model_Source_Abstract
{
    const LOCKED = 1;
    const UNLOCKED = 0;

    const LOCKED_LABEL = 'Locked';
    const UNLOCKED_LABEL = 'Unlocked';

    public function toOptionArray()
    {
        $_helper = $this->_getHelper();
        return array(
            self::LOCKED   => $_helper->__(self::LOCKED_LABEL),
            self::UNLOCKED => $_helper->__(self::UNLOCKED_LABEL)
        );
    }
}