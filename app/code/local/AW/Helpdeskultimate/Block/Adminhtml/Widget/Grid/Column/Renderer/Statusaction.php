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

class AW_Helpdeskultimate_Block_Adminhtml_Widget_Grid_Column_Renderer_Statusaction
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    /**
     * Transform data to value=>label
     * @param array $arr
     * @param array $props
     * @return array
     */
    protected function _transformData($arr, $props)
    {
        $new = array();
        if (is_array($arr) && count($arr)) {
            foreach ($arr as $k => $v) {
                $url = $props['url'];
                $url['params']['csid'] = $k;
                $new[] = array('caption' => $v, 'url' => $url, 'field' => 'id');
            }
        }
        return $new;
    }

    /**
     * Retrives rendered column
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        $actions = $this->_transformData($actions[$row->getStoreId()], $this->getColumn()->getProps());
        $out = '<select class="action-select" onchange="varienGridAction.execute(this);">'
               . '<option value=""></option>';
        $i = 0;
        foreach ($actions as $action) {
            $i++;
            if (is_array($action)) {
                $out .= $this->_toOptionHtml($action, $row);
            }
        }
        $out .= '</select>';
        return $out;
    }
}