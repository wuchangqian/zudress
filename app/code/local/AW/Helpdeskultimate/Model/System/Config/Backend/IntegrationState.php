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

class AW_Helpdeskultimate_Model_System_Config_Backend_IntegrationState extends Mage_Core_Model_Config_Data
{
    /**
     * Set attribute default value if value empty
     *
     * @param Varien_Object $object

    /**
     * Set attribute default value if value empty
     *
     * @param Varien_Object $object
     */

    const XML_PATH_SELF_ADDRESS = 'helpdeskultimate/imap/email';

    const XML_PATH_PQ_ADDRESS = 'productquestions/email/recipient_email';
    const XML_PATH_SELF_PQ_ADDRESS = 'helpdeskultimate/storage/pq_email';

    const XML_PATH_CF_ADDRESS = 'contacts/email/recipient_email';
    const XML_PATH_SELF_CF_ADDRESS = 'helpdeskultimate/storage/cf_email';

    public function _beforeSave()
    {
        $data = $this->getData();
        // If enabled, we should set field value for PQ
        if (@$data['groups']['modules']['fields']['pq_enabled']['value']) {
            /**
             * If PQ is enabled we should save original PQ address to our config, if it differs
             * from already saved
             */
            if (Mage::getStoreConfig(self::XML_PATH_PQ_ADDRESS) != Mage::getStoreConfig(self::XML_PATH_SELF_ADDRESS)) {
                Mage::getModel('adminhtml/config_data')
                    ->setSection('helpdeskultimate')
                    ->setWebsite(0)
                    ->setStore(0)
                    ->setGroups(
                        array(
                            'storage' => array(
                                'fields' => array(
                                    'pq_email' => array('value' => Mage::getStoreConfig(self::XML_PATH_PQ_ADDRESS))
                                )
                            )
                        )
                    )
                    ->save();
                if (Mage::getStoreConfig(self::XML_PATH_SELF_ADDRESS)) {
                    Mage::getModel('adminhtml/config_data')
                        ->setSection('productquestions')
                        ->setWebsite(0)
                        ->setStore(0)
                        ->setGroups(
                            array(
                                'email' => array(
                                    'fields' => array(
                                        'recipient_email' => array(
                                            'value' => Mage::getStoreConfig(self::XML_PATH_SELF_ADDRESS)
                                        )
                                    )
                                )
                            )
                        )
                        ->save();
                }
            }
        } else {
            if (
                Mage::getStoreConfig(self::XML_PATH_SELF_PQ_ADDRESS)
                && (Mage::getStoreConfig(self::XML_PATH_PQ_ADDRESS) != Mage::getStoreConfig(self::XML_PATH_SELF_PQ_ADDRESS))
                &&  Mage::getStoreConfig(self::XML_PATH_SELF_PQ_ADDRESS)
            ) {
                Mage::getModel('adminhtml/config_data')
                    ->setSection('productquestions')
                    ->setWebsite(0)
                    ->setStore(0)
                    ->setGroups(
                        array(
                            'email' => array(
                                'fields' => array(
                                    'recipient_email' => array(
                                        'value' => Mage::getStoreConfig(self::XML_PATH_SELF_PQ_ADDRESS)
                                    )
                                )
                            )
                        )
                    )
                    ->save();
            }
        }

        /* Contact form */
        // If enabled, we should set field value for CF
        /**
         * @deprecated
         **/
        if (0 && @$data['groups']['modules']['fields']['cf_enabled']['value']) {
            if (Mage::getStoreConfig(self::XML_PATH_CF_ADDRESS) != Mage::getStoreConfig(self::XML_PATH_SELF_ADDRESS)) {
                Mage::getModel('adminhtml/config_data')
                    ->setSection('helpdeskultimate')
                    ->setWebsite(0)
                    ->setStore(0)
                    ->setGroups(
                        array(
                            'storage' => array(
                                'fields' => array(
                                    'cf_email' => array('value' => Mage::getStoreConfig(self::XML_PATH_CF_ADDRESS))
                                )
                            )
                        )
                    )
                    ->save();
                Mage::getModel('adminhtml/config_data')
                    ->setSection('contacts')
                    ->setWebsite(0)
                    ->setStore(0)
                    ->setGroups(
                        array(
                            'email' => array(
                                'fields' => array(
                                    'recipient_email' => array(
                                        'value' => Mage::getStoreConfig(self::XML_PATH_SELF_ADDRESS)
                                    )
                                )
                            )
                        )
                    )
                    ->save();
            }
        } elseif (0) {
            if (
                Mage::getStoreConfig(self::XML_PATH_SELF_CF_ADDRESS)
                && (Mage::getStoreConfig(self::XML_PATH_CF_ADDRESS) != Mage::getStoreConfig(self::XML_PATH_SELF_CF_ADDRESS))
            ) {
                Mage::getModel('adminhtml/config_data')
                    ->setSection('contacts')
                    ->setWebsite(0)
                    ->setStore(0)
                    ->setGroups(
                        array(
                            'email' => array(
                                'fields' => array(
                                    'recipient_email' => array(
                                        'value' => Mage::getStoreConfig(self::XML_PATH_SELF_CF_ADDRESS)
                                    )
                                )
                            )
                        )
                    )
                    ->save();
            }
        }
        return parent::_beforeSave();
    }
}