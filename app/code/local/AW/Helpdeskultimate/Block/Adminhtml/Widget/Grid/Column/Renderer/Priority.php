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

class AW_Helpdeskultimate_Block_Adminhtml_Widget_Grid_Column_Renderer_Priority
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    /**
     * Render a grid cell as options
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $html = parent::render($row);
        $value = $row->getData($this->getColumn()->getIndex());
        switch ($value) {
            case AW_Helpdeskultimate_Model_Source_Ticket_Priority::URGENT:
                $color = '#f00';
                break;
            case AW_Helpdeskultimate_Model_Source_Ticket_Priority::ASAP:
                $color = '#900';
                break;
            default:
                $color = '#000';
        }
        return "<span style=\"color:$color\">" . $html . "</span>";
    }
}
