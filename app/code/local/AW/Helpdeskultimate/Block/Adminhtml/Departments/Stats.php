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

class AW_Helpdeskultimate_Block_Adminhtml_Departments_Stats extends Mage_Adminhtml_Block_Template
{

    const MINDATE = '1970-01-01 00:00:00';
    const MAXDATE = '2100-12-31 23:59:59';


    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('helpdeskultimate/departments/stats.phtml');
        $this->setTitle($this->__("Departments Statistics"));
        $this->_prepareCollection();
    }


    protected function _prepareLayout()
    {
        $refreshButton =

        $this->setChild('refresh_button', $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'label'   => $this->__('Refresh'),
            'onclick' => "$('aw_hdu_stats_form').submit()",
            'class'   => 'task',
        )));
        $this->setChild('reset_button', $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'label'   => $this->__('Reset'),
            'onclick' => "$('period_date_to').value='';$('period_date_from').value='';$('aw_hdu_stats_form').submit();",
            'class'   => 'task',
        )));
        parent::_prepareLayout();
        return $this;
    }

    public function addFromTo($from, $to)
    {
        Mage::register('aw_hdu_departments_fromto', array(
            date('Y-m-d H:i:s', $from),
            date('Y-m-d H:i:s', $to)
        ));
    }


    public function getFromTo()
    {
        if ($ff = Mage::registry('aw_hdu_departments_fromto_raw')) {
            return $ff;
        }
        return array('', '');
    }

    public function getStatusName($id)
    {
        $obj = Mage::getSingleton('helpdeskultimate/status')->getOptionArray();
        return $obj[$id];
    }

    public function getActionUrl()
    {
        $url = ($this->getRequest()->getParam('store'))
                ? 'helpdeskultimate_admin/departments/stats/store/' . $this->getRequest()->getParam('store')
                : 'helpdeskultimate_admin/departments/stats/';
        return Mage::getSingleton('adminhtml/url')
                ->getUrl($url);
    }


    protected function _prepareCollection()
    {
        $collection = Mage::getModel('helpdeskultimate/department')->getCollection();
        $this->setCollection($collection);
    }


    public function getDateFormat()
    {
        return $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    public function getLocale()
    {
        if (!$this->_locale) {
            $this->_locale = Mage::app()->getLocale();
        }
        return $this->_locale;
    }

    public function getTicketsUrl($dep, $status = null, $store = null)
    {
        $params = array();
        $params['department'] = $dep['id'];
        if (!is_null($status)) {
            $params['status'] = $status;
        }
        if (!is_null($store)) {
            $params['store'] = $store;
        }
        return Mage::getSingleton('adminhtml/url')->getUrl('helpdeskultimate_admin/index/index', $params);
    }

    public function getStatuses()
    {
        return Mage::getSingleton('helpdeskultimate/status')->getOptionArray();
    }

    public function getGrandTotal($array)
    {
        $result = array();
        foreach ($array as $ar) {
            $result[0] = (isset($result[0])) ? $result[0] + $ar['total'] : $ar['total'];
            foreach ($ar['stats'] as $key => $value) {
                $result[$key] = (isset($result[$key])) ? $result[$key] + $value : $value;
            }
        }
        return $result;
    }

    public function getDepartments($store = null, $from = null, $to = null)
    {
        $depsCollection = Mage::getModel('helpdeskultimate/department')->getCollection();
        $depsCollection->getSelect()->order('name ASC');
        $deps = $depsCollection->getData();
        unset($depsCollection);
        $resource = Mage::getSingleton('core/resource');
        $db = $resource->getConnection('core_read');

        $departments = array();

        foreach ($deps as $dep) {
            $statuses = array();

            $select = $db->select()
                    ->from(
                        array('ticket' => $resource->getTableName('helpdeskultimate/ticket')),
                        array('status', 'count(*) as total')
                    )
                    ->group('status')
                    ->where('department_id =?', $dep['id']);
            if ($store)
                $select->where('store_id =?', $store);

            if ($from)
                $select->where('created_time >= ?', date('Y-m-d H:i:s', strtotime($from)));

            if ($to)
                $select->where('created_time <= ?', date('Y-m-d H:i:s', strtotime($to) + 86399));

            $result = $db->fetchAll($select);

            foreach ($result as $res) {
                $statuses[$res['status']] = $res['total'];
            }

            $total = 0;
            foreach ($statuses as $stat) {
                $total += (int)$stat;
            }
            foreach ($this->getStatuses() as $key => $stat) {
                $departments[$dep['id']]['stats'][$key] = (isset($statuses[$key])) ? $statuses[$key] : 0;
            }
            $departments[$dep['id']]['label'] = $dep['name'];
            $departments[$dep['id']]['status'] = $dep['enabled'];
            $departments[$dep['id']]['total'] = $total;
            $departments[$dep['id']]['id'] = $dep['id'];
        }
        return $departments;
    }

}
