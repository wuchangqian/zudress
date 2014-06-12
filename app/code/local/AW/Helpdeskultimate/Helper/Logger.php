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

class AW_Helpdeskultimate_Helper_Logger extends AW_Helpdeskultimate_Helper_Abstract
{
    const HDU_LOG_TAB_KEY = 'hdu_lock_tab_key';
    const HDU_LOG_STORAGE_KEY = 'hdu_log_storage_key';

    public function releaseLog($title)
    {
        $logText = '';
        foreach ($this->_getLog() as $line) {
            $logText .= "\n" . $line;
        }
        $title = $this->__($title);
        Mage::helper('awcore/logger')->log($this, $title, null, ltrim($logText));
        return $this;
    }

    public function clearLogMemory()
    {
        Mage::unregister(self::HDU_LOG_STORAGE_KEY);
        Mage::unregister(self::HDU_LOG_TAB_KEY);
        return $this;
    }

    public function log()
    {
        $args = func_get_args();
        $msg = call_user_func_array('__', array_values($args));
        $msg = $this->_getMessageWithTabs($msg);
        $this->_addLog($msg);
        return $this;
    }

    public function addTab()
    {
        if ($_tabCount = Mage::registry(self::HDU_LOG_TAB_KEY)) {
            $_tabCount++;
        } else {
            $_tabCount = 1;
        }
        $this->setTabCount($_tabCount);
        return $this;
    }

    public function removeTab()
    {
        if ($_tabCount = Mage::registry(self::HDU_LOG_TAB_KEY)) {
            $_tabCount = (($_tabCount > 0)?($_tabCount-1):0);
        } else {
            $_tabCount = 1;
        }
        $this->setTabCount($_tabCount);
        return $this;
    }

    public function setTabCount($count)
    {
        Mage::unregister(self::HDU_LOG_TAB_KEY);
        Mage::register(self::HDU_LOG_TAB_KEY, $count);
        return $this;
    }

    public function getTabCount()
    {
        if ($_tabCount = Mage::registry(self::HDU_LOG_TAB_KEY)) {
            return $_tabCount;
        }
        return 0;
    }

    /*adding tabs to $msg = string*/
    protected function _getMessageWithTabs($msg)
    {
        $_tabCount = $this->getTabCount();
        $_tabString = "";
        while ($_tabCount > 0) {
            $_tabString .= "\t";
            $_tabCount--;
        }
        return $_tabString . $msg;
    }

    protected function _addLog($msg)
    {
        $_storage = Mage::registry(self::HDU_LOG_STORAGE_KEY);
        if (is_null($_storage)) {
            $_storage = array();
        }
        array_push($_storage, $msg);
        Mage::unregister(self::HDU_LOG_STORAGE_KEY);
        Mage::register(self::HDU_LOG_STORAGE_KEY, $_storage);
        return $this;
    }

    protected function _getLog()
    {
        $_storage = Mage::registry(self::HDU_LOG_STORAGE_KEY);
        if (is_null($_storage)) {
            $_storage = array();
        }
        return $_storage;
    }

}
