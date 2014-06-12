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


class AW_Helpdeskultimate_Helper_Config extends AW_Helpdeskultimate_Helper_Abstract
{
    // POP3 server
    const XML_PATH_SERVER = 'helpdeskultimate/imap/server';
    // POP3 server port
    const XML_PATH_PORT = 'helpdeskultimate/imap/port';

    // POP3 Account login
    const XML_PATH_LOGIN = 'helpdeskultimate/imap/login';
    // POP3 Account password
    const XML_PATH_PASSWORD = 'helpdeskultimate/imap/password';
    // Use SSL, TLS connection or not
    const XML_PATH_SSL = 'helpdeskultimate/imap/ssl';
    // Select type - POP3 or IMAP
    const XML_PATH_TYPE = 'helpdeskultimate/imap/type';
    const XML_PATH_SELF_EMAIL = 'helpdeskultimate/imap/email';

    const XML_PATH_CF_EMAIL = 'contacts/email/recipient_email';
    const XML_PATH_STORED_CF_EMAIL = 'helpdeskultimate/storage/cf_email';

    // Enable contact form parser
    const XML_PATH_USE_CONTACTFORM = 'helpdeskultimate/modules/cf_enabled';
    // Enable contact form standard messages
    const XML_PATH_CONTACTFORM_DISABLE_STANDART_EMAIL = 'helpdeskultimate/modules/cf_disable_email';
    // Contact form sender
    const XML_PATH_CONTACTFORM_SENDER = 'contacts/email/sender_email_identity';

    // Enable pq parser
    const XML_PATH_USE_PQ = 'helpdeskultimate/modules/pq_enabled';
    // pq sender
    const XML_PATH_PQ_SENDER = 'productquestions/email/sender_email_identity';

    // Allow new tickets from email
    const XML_PATH_ALLOWNEW = 'helpdeskultimate/advanced/allownew';
    // Allow external links
    const XML_PATH_ALLOWEXTERNAL = 'helpdeskultimate/advanced/allowexternal';

    /**
     * General Department settings. This settings does not appear on backend.
     * It necessary for old version compatibility.
     */
    const XML_PATH_GENERALDEP_CONTACT = 'helpdeskultimate/generaldep/contact';
    const XML_PATH_GENERALDEP_NAME = 'helpdeskultimate/generaldep/name';
    const XML_PATH_GENERALDEP_NOTIFY = 'helpdeskultimate/generaldep/notify';
    const XML_PATH_GENERALDEP_SENDER = 'helpdeskultimate/generaldep/sender';
    const XML_PATH_GENERALDEP_TO_ADMIN_NEW_EMAIL = 'helpdeskultimate/generaldep/to_admin_new_email';
    const XML_PATH_GENERALDEP_TO_ADMIN_REPLY_EMAIL = 'helpdeskultimate/generaldep/to_admin_reply_email';
    const XML_PATH_GENERALDEP_TO_CUSTOMER_NEW_EMAIL = 'helpdeskultimate/generaldep/to_customer_new_email';
    const XML_PATH_GENERALDEP_TO_CUSTOMER_REPLY_EMAIL = 'helpdeskultimate/generaldep/to_customer_reply_email';
    const XML_PATH_GENERALDEP_NEW_FROM_ADMIN_TO_CUSTOMER = 'helpdeskultimate/generaldep/new_from_admin_to_customer';

    const XML_PATH_ADVANCED_MANAGEFILES = 'helpdeskultimate/advanced/managefiles';

    const XML_PATH_ANTIBOT_CF_ANTIBOT = 'helpdeskultimate/antibot/cf_antibot';
    const XML_PATH_ANTIBOT_PQ_ANTIBOT = 'helpdeskultimate/antibot/pq_antibot';

    const DEFAULT_MIME_TYPE = "text/plain";
    const STORAGE_ENCODING = 'UTF-8';

    //NEW CONFIG
    const XML_PATH_TICKET_EXPIRE_AFTER = 'helpdeskultimate/advanced/ticketexpire';
    const XML_PATH_ORDERS_ENABLED = 'helpdeskultimate/advanced/orders_enabled';
    const XML_PATH_MAX_UPLOAD_FILE_SIZE = 'helpdeskultimate/advanced/maxupload';

    /**
     * is allowed creating new ticket form email then return TRUE, else FALSE
     */
    public function isAllowedCreateNewTicketFromEmail($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_ALLOWNEW, $storeId);
    }

    /**
     * getting ticket expiring limit in days
     * @return void
     */
    public function getTicketExpireAfterDays($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_TICKET_EXPIRE_AFTER, $storeId);
    }

    /**
     * is integration with contact form enabled
     */
    public function isIntegrationWithContactFormEnabled($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_USE_CONTACTFORM, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isStandartContactFormDisabled($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_CONTACTFORM_DISABLE_STANDART_EMAIL, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isIntegrationWithPQEnabled($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_USE_PQ, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isAllowedManageFiles($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_ADVANCED_MANAGEFILES, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isAllowedExternalView($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_ALLOWEXTERNAL, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isAllowedOrdersLinking($storeId = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_ORDERS_ENABLED, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getAttachmentsMaxUploadFileSize($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_MAX_UPLOAD_FILE_SIZE, $storeId);
    }

    public function getConfig($key, $storeId = null)
    {
        return Mage::getStoreConfig('helpdeskultimate/' . $key, $storeId);
    }
}
