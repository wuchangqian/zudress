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

class AW_Helpdeskultimate_Helper_Parser extends AW_Helpdeskultimate_Helper_Abstract
{

    public function isDepartmentAuthor($message)
    {
        if ($message instanceof AW_Helpdeskultimate_Model_Message && $message->isDepartmentReply()) {
            return true;
        } elseif ($message instanceof AW_Helpdeskultimate_Model_Ticket && $message->getCreatedBy() == 'admin') {
            return true;
        }
        return false;
    }
    
    /*
     * @deprecated
     */
    public function getImageBlock($attributes)
    {
        $html = "";
        if (count($attributes) > 0) {
            $html .= '<div id="image-list-box">';
            $html .= '<h4>' . $this->__('Images') . '</h4>';
            $html .= '<ul class="image-list">';

            foreach ($attributes as $_item) {
                if (isset($_item['src']) && !empty($_item['src'])) {
                    $html .= '<li>';
                        if (isset($_item['title']) && (strlen($_item['title']) > 0)) {
                            $html .= $_item['title'];
                        } elseif (isset($_item['alt']) && (strlen($_item['alt']) > 0)) {
                            $html .= $_item['alt'];
                        } else {
                            $html .= $this->__('Image');
                        }
                    $html .= "&nbsp;<a href=" . $_item['src'] . " title = " .$_item['title']
                           . " alt = " . $_item['alt'] . ">" . $_item['src'] . "</a>";
                    $html .= "</li>";
                }
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        return $html;
    }

    public function getSmartText($originText, $isHtml = false)
    {
        /* URLS replace url to link */
        if (!$isHtml) {
            $_regexpUrls = '#([^"])((http\://|http(s)\://|(www))([a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(/\S*)?))([^"])#is';
            $_replacement = "$1<a href ='http$4://$5$6'>$2</a>$8";
            $text = preg_replace($_regexpUrls, $_replacement, $originText);
        } else {
            $text = $originText;
        }

        /* Replace ticket UIDS for links to ticket*/
        $linkTemplate = "<a href='{{href}}'>{{name}}</a>";
        preg_match_all('/#[A-Z]{3}-[0-9]{5}/i', $text, $matches);
        foreach ($matches[0] as $uid) {
            $_simpleUid = str_replace('#', '', $uid);
            $ticket = Mage::getModel('helpdeskultimate/ticket')->loadByUid($_simpleUid);
            if ($ticket->hasId()) {
                $_linkText = str_replace('{{href}}', $ticket->getUrl(), $linkTemplate);
                $_linkText = str_replace('{{name}}', $uid, $_linkText);
                $text = str_replace($uid, $_linkText, $text);
            }
        }

        return $text;
    }
}