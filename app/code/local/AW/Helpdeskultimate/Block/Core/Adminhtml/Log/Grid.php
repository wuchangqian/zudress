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

class AW_Helpdeskultimate_Block_Core_Adminhtml_Log_Grid extends AW_Core_Block_Adminhtml_Log_Grid
{
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->rewriteColumnRenderer('content', 'helpdeskultimate/adminhtml_widget_grid_column_renderer_content');
    }

    /**
     *
     */
    private function rewriteColumnRenderer($columnId, $rendererClass)
    {
        if ($_column = $this->getColumn($columnId)) {
            $_renderer = $this->getLayout()->createBlock($rendererClass)
                ->setColumn($_column);
            $_column->setRenderer($_renderer);
        }
        return $this;
    }
}
