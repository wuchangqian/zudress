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

class AW_Helpdeskultimate_Helper_Imap extends AW_Helpdeskultimate_Helper_Abstract
{
    private $_patterns = null;

    /**
     * Checking message to match rejecting patterns
     * @param type $message
     * @return Integer id of the pattern or false when no matches found
     */
    public function matchRejectingPatterns($message)
    {
        if ($this->_patterns === null) {
            $this->_patterns = Mage::getModel('helpdeskultimate/rpattern')->getCollection();
            $this->_patterns->addActiveFilter();
        }
        foreach ($this->_patterns as $pattern) {
            if ($pattern->match($message)) return $pattern->getId();
        }
        return false;
    }

    /**
     * Returns ticket UID from subject
     * @param string $subject
     * @return string|bool
     */
    public function getTicketUid($subject)
    {
        if (preg_match("/\[#([a-z]{3}-[0-9]{5})\]/i", $subject, $matches)) {
            return strtoupper(@$matches[1]);
        } else
            return false;
    }

    /**
     * Parses email address from string
     * @param string $str
     * @return string
     */
    public function parseEmailAddress($str)
    {
        preg_match("/([a-z0-9.\-_]+@[a-z0-9.\-_]+)/i", $str, $matches);
        return @$matches[1];
    }

}
