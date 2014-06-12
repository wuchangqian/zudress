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

class AW_Helpdeskultimate_Helper_Threads extends AW_Helpdeskultimate_Helper_Abstract
{

    const DEFAULT_CONTENT_TYPE = 'text/plain';
    const DEFAULT_ENCODING = "UTF-8";
    const DEFAULT_QUOTE_TEXT = "Quote";

    public function quot2html($text)
    {
        // Converts [quot] tags  to html
        return preg_replace(
            '/\[quot\s+name="(.*)"\](.*)\[\/quot\]/Usie',
            "'<fieldset>
                <legend>$1</legend>'.trim(stripslashes('$2')).'</fieldset>'",
            $text
        );
    }

    public function quot2email($text)
    {
        // Converts [quot] tags  to html, safe to use in email templates
        return preg_replace(
            '/\[quot\s+name="(.*)"\](.*)\[\/quot\]/Usi',
            '
            <blockquote><b>' . Mage::helper('helpdeskultimate')->__("") . ' $1</b>
            $2
            </blockquote>
            ',
            $text
        );
    }


    public function email2internal($txt, $name = "Quote", $isText = null)
    {
        if (!$isText) {
            $txt = self::html2text($txt);
        } else {
            $txt = $txt;
        }
        return $txt;
    }

    public function getTextLikeQuot($text, $name)
    {
        // Returns text w/o quoting. To insert.
        $text = trim($text);
        $txt = preg_replace(
            '/\[quot\s+name="(.*)"\](.*)\[\/quot\]/Usi',
            '',
            $text
        );
        if (!trim($name)) $name = "Quote";
        return "[quot name=\"" . $name . "\"]" . trim(htmlentities($txt, null, "UTF-8")) . "[/quot]";
    }

    public static function html2text($txt)
    {
        // Converts html 2 text
        $text = trim(stripslashes($txt));
        // Convert <PRE>

        $preSearch = array(
            "/\n/",
            "/\t/",
            '/ /',
            '/<pre[^>]*>/',
            '/<\/pre>/'
        );

        $preReplace = array(
            '<br>',
            '&nbsp;&nbsp;&nbsp;&nbsp;',
            '&nbsp;',
            '',
            ''
        );

        while (preg_match('/<pre[^>]*>(.*)<\/pre>/ismU', $text, $matches)) {
            $result = preg_replace($preSearch, $preReplace, $matches[1]);
            $text = preg_replace('/<pre[^>]*>.*<\/pre>/ismU', '<div><br>' . $result . '<br></div>', $text, 1);
        }

        // Replace known html entities
        $text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');

        // Search & replace

        $search = array(
            "/\r/", // Non-legal carriage return
            "/[\n\t]+/", // Newlines and tabs
            '/[ ]{2,}/', // Runs of spaces, pre-handling
            '/<script[^>]*>.*?<\/script>/i', // <script>s -- which strip_tags supposedly has problems with
            '/<style[^>]*>.*?<\/style>/i', // <style>s -- which strip_tags supposedly has problems with
            //'/<!-- .* -->/',                         // Comments -- which strip_tags might have problem a with
            '/<p[^>]*>/i', // <P>
            '/<br[^>]*>/i', // <br>
            '/<i[^>]*>(.*?)<\/i>/i', // <i>
            '/<em[^>]*>(.*?)<\/em>/i', // <em>
            '/(<ul[^>]*>|<\/ul>)/i', // <ul> and </ul>
            '/(<ol[^>]*>|<\/ol>)/i', // <ol> and </ol>
            '/<li[^>]*>(.*?)<\/li>/i', // <li> and </li>
            '/<li[^>]*>/i', // <li>
            '/<hr[^>]*>/i', // <hr>
            '/(<table[^>]*>|<\/table>)/i', // <table> and </table>
            '/(<tr[^>]*>|<\/tr>)/i', // <tr> and </tr>
            '/<td[^>]*>(.*?)<\/td>/i', // <td> and </td>
            '/&(nbsp|#160);/i', // Non-breaking space
            '/&(quot|rdquo|ldquo|#8220|#8221|#147|#148);/i',
            // Double quotes
            '/&(apos|rsquo|lsquo|#8216|#8217);/i', // Single quotes
            '/&gt;/i', // Greater-than
            '/&lt;/i', // Less-than
            '/&(amp|#38);/i', // Ampersand
            '/&(copy|#169);/i', // Copyright
            '/&(trade|#8482|#153);/i', // Trademark
            '/&(reg|#174);/i', // Registered
            '/&(mdash|#151|#8212);/i', // mdash
            '/&(ndash|minus|#8211|#8722);/i', // ndash
            '/&(bull|#149|#8226);/i', // Bullet
            '/&(pound|#163);/i', // Pound sign
            '/&(euro|#8364);/i', // Euro sign
            '/&[^&;]+;/i', // Unknown/unhandled entities
            '/[ ]{2,}/' // Runs of spaces, post-handling
        );

        $replace = array(
            '', // Non-legal carriage return
            ' ', // Newlines and tabs
            ' ', // Runs of spaces, pre-handling
            '', // <script>s -- which strip_tags supposedly has problems with
            '', // <style>s -- which strip_tags supposedly has problems with
            //'',                                     // Comments -- which strip_tags might have problem a with
            "\n\n", // <P>
            "\n", // <br>
            '_\\1_', // <i>
            '_\\1_', // <em>
            "\n\n", // <ul> and </ul>
            "\n\n", // <ol> and </ol>
            "\t* \\1\n", // <li> and </li>
            "\n\t* ", // <li>
            "\n-------------------------\n", // <hr>
            "\n\n", // <table> and </table>
            "\n", // <tr> and </tr>
            "\t\t\\1\n", // <td> and </td>
            ' ', // Non-breaking space
            '"', // Double quotes
            "'", // Single quotes
            '>',
            '<',
            '&',
            '(c)',
            '(tm)',
            '(R)',
            '--',
            '-',
            '*',
            '??',
            'EUR', // Euro sign. ??? ?
            '', // Unknown/unhandled entities
            ' ' // Runs of spaces, post-handling
        );

        $text = preg_replace($search, $replace, $text);

        // Strip any other HTML tags
        $text = strip_tags($text);

        // Bring down number of empty lines to 2 max
        $text = preg_replace("/\n\s+\n/", "\n\n", $text);
        $text = preg_replace("/[\n]{3,}/", "\n\n", $text);

        $text = wordwrap($text, 255);
        return $text;
    }

    public function processLinks($text)
    {
        return preg_replace("/(?<!\")(https?|ftp):\\/\\/([^><\s]+)/si", '<a href="\\1://\\2">\\1://\\2</a>', $text);
    }

    /**
     * Returns formatted message for ticket or message
     * @param object $item
     * @param object $noBr [optional]
     * @return
     */
    public function getContentHtml($item, $noBr = false)
    {
        $item->getContentType() || $item->setContentType(self::DEFAULT_CONTENT_TYPE);

        $text = $item->getContent();

        switch ($item->getContentType()) {
            case 'text/html':
            case 'html':
                $text = $this->html2text($text);
                break;
            case 'text/plain':
                break;
        }
        $text = (
        $this->quot2html(trim(htmlentities($text, null, self::DEFAULT_ENCODING))));
        return $noBr ? $text : nl2br($this->processLinks($text));
    }

    /**
     * Returns text-formatted content for message or ticket with "quot marks"
     * @param object $item
     * @return
     */
    public function getContentAsQuot($item)
    {
        $item->getContentType() || $item->setContentType(self::DEFAULT_CONTENT_TYPE);
        $text = $item->getContent();

        switch ($item->getContentType()) {
            case 'text/html':
            case 'html':
                $text = $this->html2text($text);
                break;
            case 'text/plain':
                break;

        }

        $text = trim($text);
        $text = preg_replace(
            '/\[quot\s+name="(.*)"\](.*)\[\/quot\]/Usi',
            '',
            $text
        );

        $item->getAuthorName() || $item->setAuthorName(self::DEFAULT_QUOTE_TEXT);
        return "[quot name=\"" . $item->getAuthorName() . "\"]"
             . trim(htmlentities($text, null, self::DEFAULT_ENCODING))
             . "[/quot]";
    }

    public function t2jsmls($text)
    {
        if (strpos($text, "\r\n"))
            $text = str_replace("\r\n", "\\n\\\r\n", $text);
        else
            $text = str_replace("\n", "\\n\\\r\n", $text);
        return $text;
    }

    public function stringToJsString($string)
    {
        $string = addslashes($string);
        return preg_replace("/[\n\r]{1,2}/", "\\n\\\r\n", $string);
    }

    public function convertStringToSize($string, $size)
    {
        return Mage::helper('core/string')->truncate($string, $size);
    }
}
