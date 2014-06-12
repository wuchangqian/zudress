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

class AW_Helpdeskultimate_Model_Proto_Source_Abstract extends AW_Core_Object
{
    /**
     * Returns founded customer or preset entity
     * @return Mage_Customer_Model_Customer | Varien_Object
     */
    public function getCustomer(AW_Helpdeskultimate_Model_Proto $proto)
    {
        if ($customer = $this->_findCustomer($this->getFromEmail($proto))) {
            // Found existing customer
            return $customer;
        } else {
            // No customer found
            $customer = new Varien_Object;
            $customer->setName($proto->getFrom())
                ->setEmail($this->getFromEmail($proto));
            return $customer;
        }
    }

    /**
     * Gets email from string
     * @param string $str
     * @return string
     */
    protected function _parseEmail($str)
    {
        if (preg_match("/([a-z0-9.\-_]+@[a-z0-9.\-_]+)/i", $str, $matches)) {
            return strtolower(@$matches[1]);
        } else {
            $this->log("Can't parse email from '$str' while processing proto #{$this->getId()}");
            return false;
        }
    }

    /**
     * Returns parsed email from 'from' field
     * @return str
     */
    public function getFromEmail($proto)
    {
        return $this->_parseEmail($proto->getFrom());
    }

    /**
     * Returns customer find by email or false if no customer is found
     * @param object $email
     * @return Mage_Customer_Model_Customer
     */
    public function _findCustomer($email)
    {
        // Tries to resolve customer by email address
        $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tbl = Mage::getModel('customer/entity_customer')->getEntityTable();
        $res = $conn->query(
            "SELECT entity_id FROM {$tbl} WHERE email='$email'"
        );
        while ($row = $res->fetch()) {
            return Mage::getModel('customer/customer')->load(@$row['entity_id']);
        }
        return false;
    }
}