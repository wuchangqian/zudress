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


class AW_Followupemail_Model_Subscriber extends AW_Followupemail_Model_SubscriberCommon
{
    public function unsubscribe()
    {
        parent::unsubscribe();

        if (self::STATUS_UNSUBSCRIBED == $this->getSubscriberStatus()
            && Mage::getStoreConfig('followupemail/general/sendonlytosubscribers')
        ) {
            Mage::getModel('followupemail/mysql4_queue')->deleteByCustomerEmail($this->getSubscriberEmail());

            AW_Followupemail_Model_Log::logWarning('email ' . $this->getSubscriberEmail() . ' unsubscribed, all emails that were scheduled from now and not sent were deleted from the email queue');
        }
        return $this;
    }
}