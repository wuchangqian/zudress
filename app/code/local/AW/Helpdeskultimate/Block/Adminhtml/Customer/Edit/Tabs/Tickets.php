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
 * @copyright  Copyright (c) 2008-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 * @version    1.0
 */

class AW_Helpdeskultimate_Block_Adminhtml_Customer_Edit_Tabs_Tickets
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Reference to product objects that is being edited
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    protected $_config = null;

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('helpdeskultimate')->__('Customer Tickets');
    }

    public function getTabTitle()
    {
        return Mage::helper('helpdeskultimate')->__('Customer Tickets');
    }

    public function canShowTab()
    {
        return $this->getId() ? true : false;
    }

    /**
     * Retrives custmer's id from request
     * @return integer
     */
    public function getId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * Check if tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getId()) {
            return '';
        }
        $id = $this->getId();
        $ticketNewUrl = $this->getUrl('helpdeskultimate_admin/ticket/new', array('customer_id' => $id));
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setClass('add')
                ->setType('button')
                ->setOnClick('window.location.href=\'' . $ticketNewUrl . '\'')
                ->setLabel($this->__('Create ticket for this customer'));


        $grid = $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_grid');

        $grid->setDefaultFilter(array('customer_id' => $id));
        $grid->setFilterVisibility(false);
        $grid->setPagerVisibility(0);
        $grid->setUserMode();
        $grid->setOnePage();

        return '<div class="content-buttons-placeholder" style="height:25px;">' .
                    '<p class="content-buttons form-buttons" >' . $button->toHtml() . '</p>' .
               '</div>' . $grid->toHtml();
    }
}
