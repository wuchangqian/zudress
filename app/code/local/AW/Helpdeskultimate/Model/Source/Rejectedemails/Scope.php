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

class AW_Helpdeskultimate_Model_Source_Rejectedemails_Scope extends AW_Helpdeskultimate_Model_Source_Abstract
{
    const HEADERS = 1;
    const SUBJECT = 2;
    const BODY = 3;

    const HEADERS_LABEL = 'Headers';
    const SUBJECT_LABEL = 'Subject';
    const BODY_LABEL = 'Body';

    public function toOptionArray()
    {
        $_helper = $this->_getHelper();
        return array(
            array('value' => self::HEADERS, 'label' => $_helper->__(self::HEADERS_LABEL)),
            array('value' => self::SUBJECT, 'label' => $_helper->__(self::SUBJECT_LABEL)),
            array('value' => self::BODY, 'label' => $_helper->__(self::BODY_LABEL))
        );
    }
}
