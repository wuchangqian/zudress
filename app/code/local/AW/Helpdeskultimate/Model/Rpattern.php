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

class AW_Helpdeskultimate_Model_Rpattern extends AW_Core_Model_Abstract
{
    private $_awhduIsLoaded = false;

    protected function _construct()
    {
        $this->_init('helpdeskultimate/rpattern');
    }

    protected function _beforeSave()
    {
        if (is_array($this->getData('scope')))
            $this->setData('scope', @implode(',', $this->getData('scope')));
        return parent::_beforeSave();
    }

    protected function _afterLoad()
    {
        if (is_string($this->getData('scope')))
            $this->setData('scope', @explode(',', $this->getData('scope')));
        return parent::_afterLoad();
    }

    public function isInScope($value)
    {
        if (is_string($this->getData('scope')))
            $this->_afterLoad();
        return is_array($this->getData('scope')) && in_array($value, $this->getData('scope'));
    }

    public function match($message)
    {
        if ($this->isInScope(AW_Helpdeskultimate_Model_Source_Rejectedemails_Scope::HEADERS)) {
            if (@preg_match($this->getData('pattern'), $message->getHeaders()))
                return true;
        }
        if ($this->isInScope(AW_Helpdeskultimate_Model_Source_Rejectedemails_Scope::SUBJECT)) {
            if (@preg_match($this->getData('pattern'), $message->getSubject()))
                return true;
        }
        if ($this->isInScope(AW_Helpdeskultimate_Model_Source_Rejectedemails_Scope::BODY)) {
            if (@preg_match($this->getData('pattern'), $message->getBody()))
                return true;
        }
        return false;
    }
}
