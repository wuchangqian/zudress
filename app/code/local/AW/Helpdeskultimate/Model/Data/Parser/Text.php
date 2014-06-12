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

class AW_Helpdeskultimate_Model_Data_Parser_Text extends AW_Helpdeskultimate_Model_Data_Parser_Abstract
{
    /**
     *
     */
    private $_pool;
    private $_inserts;

    /**
     * They expect this function to do something but its truly excessive
     */
    public function clear()
    {
        $_text = htmlspecialchars($this->getText());
        $this->setText($_text);
        return $this;
    }

    /**
     * They expect this function to do something but its truly excessive
     */
    public function convertToQuoteAsHtml()
    {
        $text = $this->clear()->getText();
        $text = $this->__microparseBlockquotes(html_entity_decode($text));
        //wrap line in <p></p>
        $text = preg_replace("/([^\n\r$]+)[^>][\n\r$]+/", "<p>$1</p>", $text);
        //remove \n \r
        $text = preg_replace("/([\n\r$]+)/", "", $text);
        $text = "<blockquote>" . $text . "</blockquote>";
        $this->setText($text);
        return $this;
    }

    /**
     * They expect this function to do something but its truly excessive
     */
    public function convertToQuoteAsText()
    {
        //$text = strip_tags($this->getText());
        $text = preg_replace("/(([^\n\r]*)([\n\r]+))/", ">$0", $this->getText() . "\n\r");
        $this->setText($text);
        return $this;
    }

    public function convertToQuote()
    {
        return $this->convertToQuoteAsText();
    }

    /**
     *
     */
    public function prepareToDisplay($isStripAttributes = true)
    {
        $_raw = $this->clear()->getText();
        $_raw = $this->__microparseBlockquotes(html_entity_decode($_raw));
        $this->_pool = $_raw;
        $this->_inserts = array();

        $_changesCount = 1;
        while ($_changesCount > 0) {
            $_changesCount = 0;
            $_changesCount += $this->__microparseLinks();
        }

        $_changesCount = 1;
        while ($_changesCount > 0) {
            $_changesCount = 0;
            $_changesCount += $this->__microparseNewlinesymbols();
        }

        $_point = 0;
        $_text = '';
        foreach ($this->_inserts as $_position => $_contentItems) {
            $_text .= substr($this->_pool, $_point, $_position - $_point);
            foreach ($_contentItems as $_item) $_text .= $_item;
            $_point = $_position;
        }
        $_text .= substr($this->_pool, $_point, strlen($this->_pool));
        //convert #AAA-11111 to link to ticket

        $smartText = Mage::helper('helpdeskultimate/parser')->getSmartText($_text, false);
        $this->setText($smartText);
        return $this;
    }


    /**
     *
     */
    private function __removeFromPool($index, $count)
    {
        $this->_pool = substr_replace($this->_pool, '', $index, $count);
        foreach ($this->_inserts as $_position => $_contentItems) {
            if ($_position > $index) {
                if (!isset($this->_inserts[$_position - $count])) $this->_inserts[$_position - $count] = array();
                foreach ($this->_inserts[$_position] as $_item) {
                    array_push($this->_inserts[$_position - $count], $_item);
                }
                unset($this->_inserts[$_position]);
            }
        }
        ksort($this->_inserts);
    }

    /**
     *
     */
    private function __insertIntoPool($position, $content)
    {
        if (!isset($this->_inserts[$position])) $this->_inserts[$position] = array();
        array_push($this->_inserts[$position], $content);
    }

    /**
     *
     */
    private function __microparseLinks()
    {
        $_changesCount = 0;
        while (preg_match(
            '/<(http|https):\/\/([\w\.]*\w+\.)([\w-]{3})([\w\/\.\?\&-]*)>/U',
            $this->_pool,
            $_matches,
            PREG_OFFSET_CAPTURE
        )) {
            $_changesCount++;
            $this->__removeFromPool($_matches[0][1], strlen($_matches[0][0]));
            $this->__insertIntoPool(
                $_matches[0][1],
                '<a href="' . $_matches[1][0] . '://' . $_matches[2][0] . $_matches[3][0] . $_matches[4][0] . '">'
                . $_matches[1][0] . '://' . $_matches[2][0] . $_matches[3][0] . $_matches[4][0] . '</a>'
            );
        }
        return $_changesCount;
    }

    /**
     *
     */
    private function __microparseBoldtext()
    {
        $_changesCount = 0;
        while (preg_match('|(\*)([\w][^\n]*[\w])(\*)|U', $this->_pool, $_matches, PREG_OFFSET_CAPTURE)) {
            $_changesCount++;
            $this->__removeFromPool($_matches[1][1], 1);
            $this->__insertIntoPool($_matches[1][1], '<b>');
            $this->__removeFromPool($_matches[3][1] - 1, 1);
            $this->__insertIntoPool($_matches[3][1] - 1, '</b>');
        }
        return $_changesCount;
    }

    /**
     *
     */
    private function __microparseItalictext()
    {
        $_changesCount = 0;
        while (preg_match('|(\/)([\w][^\n]*[\w])(\/)|U', $this->_pool, $_matches, PREG_OFFSET_CAPTURE)) {
            $_changesCount++;
            $this->__removeFromPool($_matches[1][1], 1);
            $this->__insertIntoPool($_matches[1][1], '<i>');
            $this->__removeFromPool($_matches[3][1] - 1, 1);
            $this->__insertIntoPool($_matches[3][1] - 1, '</i>');
        }
        return $_changesCount;
    }

    /**
     *
     */
    private function __microparseUnderlinedtext()
    {
        $_changesCount = 0;
        while (preg_match('|(_)([\w][^\n]*[\w])(_)|U', $this->_pool, $_matches, PREG_OFFSET_CAPTURE)) {
            $_changesCount++;
            $this->__removeFromPool($_matches[1][1], 1);
            $this->__insertIntoPool($_matches[1][1], '<u>');
            $this->__removeFromPool($_matches[3][1] - 1, 1);
            $this->__insertIntoPool($_matches[3][1] - 1, '</u>');
        }
        return $_changesCount;
    }

    /**
     *
     */
    private function __microparseNewlinesymbols()
    {
        $_changesCount = 0;
        while (preg_match('|(\n)|U', $this->_pool, $_matches, PREG_OFFSET_CAPTURE)) {
            $_changesCount++;
            $this->__removeFromPool($_matches[1][1], 1);
            $this->__insertIntoPool($_matches[1][1], "<br />\n");
        }
        return $_changesCount;
    }

    private function __microparseBlockquotes($text)
    {
        $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
        $_depth = 15;
        $_exit = false;
        while (!$_exit && $_depth > 0) {
            $_exit = true;
            $_depth--;
            //inline fragmentation
            preg_match_all("/([^\n\r]*)[\n\r]{0,2}/", $text, $matches, PREG_OFFSET_CAPTURE);

            //found blockquotes
            $replacementArray = array();
            $_currentQuote = null;
            $_currentQuotedText = "";
            foreach ($matches[1] as $quote) {
                preg_match("/^[\d]*(&gt;)(.*)/", $quote[0], $isFounded, PREG_OFFSET_CAPTURE);
                if (!isset($isFounded[2]) || is_null($isFounded[2])) {
                    if (!is_null($_currentQuote)) {
                        $_exit = false;
                        $replacementArray[] = array(
                            'from' => $_currentQuote,
                            'to'   => $quote[1],
                            'text' => $_currentQuotedText
                        );
                        $_currentQuote = null;
                        $_currentQuotedText = "";
                    }
                } else {
                    if (is_null($_currentQuote)) {
                        $_currentQuote = $quote[1];
                    }
                    if (isset($isFounded[2])) {
                        $_currentQuotedText .= $isFounded[2][0] . "\n\r";
                    } else {
                        $_currentQuotedText .= "\n\r";
                    }
                }
            }

            //create new line with blockquote tag
            $_newText = '';
            $_offset = 0;
            foreach ($replacementArray as $rule) {
                $_newText .= substr($text, $_offset, $rule['from'] - $_offset);
                $_newText .= "<blockquote>\n" . $rule['text'] . "</blockquote>\n";
                $_offset = $rule['to'];
            }
            $_newText .= substr($text, $_offset);
            $text = $_newText;
            //repeat if replacementArray not empty
        }
        //remove \n from tag end
        $text = preg_replace("/(blockquote>\n)/", "blockquote>", $text);
        return $text;
    }
}
