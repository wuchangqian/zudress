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


class AW_Followupemail_Model_Sender
{
    /*
     * @var bool Whether sending job is currently running
     */
    protected static $_isRunning = false;

    /*
     * @var int Sender run interval (in seconds)
     */
    public static $runInterval = 600; // 10 minutes = 600 seconds

    /*
     * Sends prepared emails from email queue
     */
    public static function sendPrepared()
    {
        $config = Mage::getModel('followupemail/config');

        if (!$lastExecTime = $config->getParam(AW_Followupemail_Model_Config::EMAIL_SENDER_LAST_EXEC_TIME)) {
            $config->setParam(AW_Followupemail_Model_Config::EMAIL_SENDER_LAST_EXEC_TIME, time());
            return;
        }

        if (self::$_isRunning
            || time() - $lastExecTime < self::$runInterval
        ) return;

        self::$_isRunning = true;

        $config->setParam(AW_Followupemail_Model_Config::EMAIL_SENDER_LAST_EXEC_TIME, time());

        $emails = Mage::getModel('followupemail/mysql4_queue')->getPreparedEmailIds();
        $model = Mage::getModel('followupemail/queue');

        AW_Followupemail_Model_Log::log('email sender started, ' . count($emails) . ' email(s) prepared');

        foreach ($emails as $emailId)
            $model->load($emailId)->send();

        self::$_isRunning = false;
    }

}