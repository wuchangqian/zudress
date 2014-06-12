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

class AW_Helpdeskultimate_Block_Customer_Tickets extends Mage_Core_Block_Template
{

    protected $_collection;

    public function count()
    {
        return $this->_collection->getSize();
    }

    protected function _construct()
    {
        $this->_collection = Mage::getModel('helpdeskultimate/ticket')->getCollection();
        $this->_collection
                ->addCustomerFilter(Mage::getSingleton('customer/session')->getCustomer()->getId());
    }

    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    protected function _prepareLayout()
    {
        $toolbar = $this->getLayout()->createBlock('page/html_pager', 'customer_review_list.toolbar')
                ->setCollection($this->_getCollection());

        $this->setChild('toolbar', $toolbar);
        return parent::_prepareLayout();
    }

    protected function _getCollection()
    {
        return $this->_collection;
    }

    public function getCollection()
    {
        return $this->_getCollection();
    }

    public function getTicketLink($id = null)
    {
        $args = is_null($id) ? array() : array('id' => $id);
        return Mage::getUrl('helpdeskultimate/customer/view/', $args);
    }

    public function dateFormat($date)
    {
        return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    protected function _beforeToHtml()
    {
        $this->_getCollection()
                ->orderBy('created_time DESC')
                ->load();
        return parent::_beforeToHtml();
    }
}
