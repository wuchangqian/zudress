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

abstract class AW_Helpdeskultimate_Model_Data_Parser_Abstract extends AW_Core_Object
{

    const STORAGE_ENCODING = "UTF-8";

    const RE_URL = "/[^'](((https?:\/\/)|([^\/]www)|(mailto:))([0-9@a-z\.\-]+)\.([a-z\.]{2,6})([\w\/\-\.=?;#&]*)\/?(\[([^\]]*)\]){0,1})[^']/ei";
    const RE_EMAIL = '/([a-z0-9_\.\-]+)@([a-z0-9_\.\-]+)([a-z\.]{2,6})/';

    const TAG_CITE_OPEN = "<fieldset>";
    const TAG_CITE_CLOSE = "</fieldset>";

    protected $_lastText;

    /**
     * Constructor
     * @param string $text
     * @return AW_Helpdeskultimate_Model_Data_Parser_Abstract
     */
    public function __construct($text = null)
    {
        parent::__construct();
        $this->setText($text);
    }

    /**
     * Returns content w/o quotes
     * @return string
     */
    public function getClearContent()
    {
    }

    /**
     * Prepares text to save
     * @return string
     */
    public function prepareToSave()
    {
    }

    /**
     * Prepares text to display
     * @return string
     */
    public function prepareToDisplay()
    {
    }


//	public function __toString(array $arrAttributes = array(), $valueSeparator=','){
//		return $this->getText();
//	}

    /**
     * Magic method to string conversion
     * @return string
     */
    public function toString($format = '')
    {
        return $this->getText();
    }
}
