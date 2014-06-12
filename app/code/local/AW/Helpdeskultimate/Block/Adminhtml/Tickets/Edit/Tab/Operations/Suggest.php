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

class AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tab_Operations_Suggest extends Mage_Adminhtml_Block_Widget_Form
{
    const TOTAL_COUNT = 'total records found: %s';

    public function getHtml()
    {
        $likeOrder = $this->getOptions();
        $html = "<ul>\n";
        if (count($likeOrder)) {
            foreach ($likeOrder as $item) {
                $_cssClass = $item['is_belong_to_customer'] ? 'belong-to-customer' : 'not-belong-to-customer';
                $html .= "<li id='" . $item['value'] . "' class='" . $_cssClass . "'>" . $item['label'] . "</li>\n";
            }
        } else {
            //for displaying block without items
            $html .= "<li style='display:none'>&nbsp;</li>";
        }
        $html .= "</ul>";
        $count = $this->getCount();
        $html .= "<div class='total-count'>"
               . $this->helper('helpdeskultimate')->__(self::TOTAL_COUNT, $count)
               . "</div>\n";
        return $html;
    }
}