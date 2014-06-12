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

class AW_Helpdeskultimate_Model_Data_Parser_Html extends AW_Helpdeskultimate_Model_Data_Parser_Abstract
{
    const PERMITTED_TAGS = "<ul><ol><li><blockquote><strong><table><thead><tfoot><tbody><tr><td><hr><sub><sup><code><img><br><b><i><u><a><p><em><span>";
    const RP_GET_IMG = "/<img[^>]*>/i";
    const RP_GET_ATTR = "/(src|alt|title)[^\"\']*(\"|\')[^\"\']*(\"|\')/";
    const NEW_IMG = '<div class="message-image">Image</div>';

    public function clear()
    {
        //TODO: replace strip_tags to method, which will replace unpermitted tags to htmlentities
        $_text = strip_tags($this->getText(), self::PERMITTED_TAGS);
        $this->setText($_text);
        $this->__stripUnclosedTags();
        return $this;
    }

    /**
     * Formats quotes to HTML
     * @return string
     */
    public function formatQuotes()
    {
        return $this->getText();
    }

    /**
     * Returns content w/o quotes
     * @return string
     */
    public function convertToQuoteAsHtml()
    {
        $this->clear();
        $text = "<blockquote>" . $this->getText() . "</blockquote>";
        $this->setText($text);
        return $this;
    }

    public function convertToQuoteAsText()
    {
        $this->clear();
        $text = strip_tags($this->getText());
        $text = preg_replace("/(([^\n\r]*)([\n\r]+))/", ">$0", $text . "\n\r");
        $this->setText($text);
        return $this;
    }

    public function convertToQuote()
    {
        return $this->convertToQuoteAsHtml();
    }

    /**
     * Prepares text to display
     * @return string
     */
    public function prepareToDisplay($isStripAttributes = true)
    {
        $this->clear();

        if ($isStripAttributes) {
            $this->__stripTagAttributes();
        }

        $html = Mage::helper('helpdeskultimate/parser')->getSmartText($this->getText(), true);
        $this->setText($html);
        return $this;
    }

    /* @deprecated */
    private function __microparseImages($_text = null)
    {
        if (is_null($_text)) {
            $_text = $this->getText();
        }
        $_images = $this->__getImages($_text);
        $_resultArrayOfImages = array();
        foreach ($_images as $_key => $_item) {
            while (preg_match(self::RP_GET_ATTR, $_item[0], $_res)) {
                $_resultArrayOfImages[$_key][] = $_res[0];
                $_item[0] = str_replace($_res[0], "", $_item[0]);
            }
        }
        return $_resultArrayOfImages;
    }

    private function __microparseAttributes($_attr)
    {
        $_keyPattern = '/([\w]*)=/';
        $_valuePattern = '/(\"|\')([^\"\']*)(\"|\')/';
        preg_match($_keyPattern, $_attr, $_keyOfArray);
        preg_match($_valuePattern, $_attr, $_valueOfArray);
        $_keyOfArray = $_keyOfArray[1];
        $_valueOfArray = trim($_valueOfArray[2]);
        return array($_keyOfArray, $_valueOfArray);
    }

    /* @deprecated */
    private function __replaceImages($_text = null)
    {
        if (is_null($_text)) {
            $_text = $this->getText();
        }
        return preg_replace(self::RP_GET_IMG, self::NEW_IMG, $_text);
    }

    /* @deprecated */
    private function __getImages($_text)
    {
        $_images = array();
        preg_match_all(self::RP_GET_IMG, $_text, $_images, PREG_OFFSET_CAPTURE);
        $_images = $_images[0];
        return $_images;
    }

    /*If permitted tag does not has closing tag, then this tag will been removed*/
    private function __stripUnclosedTags()
    {
        if (!defined('OPEN_TAG_FLAG')) {
            define('OPEN_TAG_FLAG', 'open');
        }
        if (!defined('CLOSE_TAG_FLAG')) {
            define('CLOSE_TAG_FLAG', 'close');
        }
        $text = $this->getText();
        $tags = array();
        preg_match_all('/<[^>]+>/', self::PERMITTED_TAGS, $tags);
        foreach ($tags[0] as $_tag) {
            if ($_tag == '<img>' || $_tag == '<br>' || $_tag == '<hr>') {
                continue;
            }
            //Find open and close tags
            $_openingTags = array();
            $_openTagPattern = '/' . substr($_tag, 0, strlen($_tag) - 1) . '[\s>]/';
            preg_match_all($_openTagPattern, $text, $_openingTags, PREG_OFFSET_CAPTURE);
            $_closingTags = array();
            $_closeTagPattern = '/<\/' . substr($_tag, 1) . '/';
            preg_match_all($_closeTagPattern, $text, $_closingTags, PREG_OFFSET_CAPTURE);

            //the following code combine matches in map array
            $_tagMap = array();
            foreach ($_openingTags[0] as $tagPos) {
                $_tagMap[$tagPos[1]] = OPEN_TAG_FLAG;
            }
            foreach ($_closingTags[0] as $tagPos) {
                $_tagMap[$tagPos[1]] = CLOSE_TAG_FLAG;
            }
            ksort($_tagMap);
            /*
             * $_tagMap = array(pos_in_text => tag_type = OPEN_TAG_FLAG|CLOSE_TAG_FLAG);
             * this array is sorted by key(pos_in_text) in ascending order.
             */
            //the following part remove valid tags $_tagMap
            $_exit = false;
            while (!$_exit) {
                $_exit = true;
                $_openTagKey = null;
                foreach ($_tagMap as $currentKey => $value) {
                    if ($value == OPEN_TAG_FLAG) {
                        $_openTagKey = $currentKey;
                    } elseif ($value == CLOSE_TAG_FLAG) {
                        if (!is_null($_openTagKey) && isset($_tagMap[$_openTagKey])) {
                            unset($_tagMap[$_openTagKey]);
                            unset($_tagMap[$currentKey]);
                            $_openTagKey = null;
                            $_exit = false;
                        }
                    }
                    //If script here then error in $_tagMap array
                }
            }

            /*
             * Now $_tagMap contain only invalid HTML tags
             */

            //The following code remove invalid tags from text
            $_newText = '';
            $_offset = 0;
            foreach ($_tagMap as $_pos => $_tagType) {
                $_newText .= substr($text, $_offset, $_pos - $_offset);
                $_offset = strpos($text, '>', $_pos) + 1;
            }
            $_newText .= substr($text, $_offset);
            $text = $_newText;
        }
        $this->setText($text);
        return $this;
    }

    /* strip attributes from all tags but not 'div' and 'a' tag*/
    private function __stripTagAttributes()
    {
        $_text = preg_replace("/<([bce-z][a-z]+)[^>]*?(\/?)>/i", "<$1$2>", $this->getText());
        $this->setText($_text);
        return $this;
    }
}