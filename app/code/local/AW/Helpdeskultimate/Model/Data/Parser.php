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

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Helpdeskultimate
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 * @version    1.0
 */

class AW_Helpdeskultimate_Model_Data_Parser extends AW_Core_Object
{

    const HTML_INSTANCE    = 'helpdeskultimate/data_parser_html';
    const TEXT_INSTANCE    = 'helpdeskultimate/data_parser_text';
    const DEFAULT_INSTANCE = self::TEXT_INSTANCE;

    private $_instance = null;
    private $_text = null;

    public function __call($name, $arguments)
    {
        return $this->getInstance()->$name($arguments);
    }

    public function getInstance($format = false, $reInit = false)
    {
        if (is_null($this->_instance) || $reInit) {
            if (is_null($this->getText()) && !$format) {
                return Mage::getModel(self::DEFAULT_INSTANCE);
            } else {
                $this->_instance = $this->__discoverInstance($format);
                $this->_instance->setText($this->getText());
            }
        }
        return $this->_instance;
    }

    public function getText()
    {
        return $this->_text;
    }

    public function setText($text, $reInit = false)
    {
        $this->_text = $text;
        if ($reInit) {
            $this->getInstance(false, true);
        }
        return $this;
    }

    /* if format invalid then method return false */
    public function setParserFormat($format)
    {
        $this->getInstance($format);
        if (is_null($this->_instance)) {
            return false;
        }
        return true;
    }

    private function __discoverInstance($format = false)
    {
        if ($format) {
            if (stristr($format, 'html') !== false) {
                return Mage::getModel(self::HTML_INSTANCE);
            } elseif (stristr($format, 'text') !== false) {
                return Mage::getModel(self::TEXT_INSTANCE);
            }
        }
        if (is_null($this->getText())) {
            $model = Mage::getModel(self::DEFAULT_INSTANCE);
            return $model;
        }
        //check about exist tags in string
        if (strip_tags($this->getText()) == $this->getText()) {
            $model = Mage::getModel(self::TEXT_INSTANCE);
        } else {
            $model = Mage::getModel(self::HTML_INSTANCE);
        }
        return $model;
    }


}