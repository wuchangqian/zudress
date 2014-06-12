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

class AW_Helpdeskultimate_Model_Proto_Source_Web extends AW_Helpdeskultimate_Model_Proto_Source_Abstract
{
    /**
     * Returns founded customer or preset entity
     * @return Mage_Customer_Model_Customer | Varien_Object
     */
    public function getCustomer(AW_Helpdeskultimate_Model_Proto $proto)
    {
        $customer = Mage::getModel('customer/customer')->load($proto->getFrom());
        if ($customer->getId()) {
            return $customer;
        } else {
            Mage::throwException("Can not load customer. Reason: customer is missing");
            return null;
        }
    }

    public function getFromEmail($proto)
    {
        return $this->getCustomer($proto)->getEmail();
    }

}