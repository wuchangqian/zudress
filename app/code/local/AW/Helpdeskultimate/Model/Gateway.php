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


class AW_Helpdeskultimate_Model_Gateway extends AW_Core_Model_Abstract
{
    const SECURE_NONE = 'none';

    protected function _construct()
    {
        $this->_init('helpdeskultimate/gateway');
    }

    /**
     * Returns according connection
     * @return AW_Helpdeskultimate_Model_Gateway_Connection
     */
    public function getConnection()
    {
        if (!$this->getData('connection')) {
            $connection = Mage::getModel('helpdeskultimate/gateway_conection')->initFromGateway($this);
            $this->setData($connection);
        }
        return $this->getData('connection');
    }

    public function getGatewayEmails($except = null)
    {
        $collection = $this->getCollection();
        if ($except !== null) {
            $collection->addFieldToFilter($collection->getResource()->getIdFieldName(), array('neq' => $except));
        }
        return $collection->getColumnValues('email');
    }
}
