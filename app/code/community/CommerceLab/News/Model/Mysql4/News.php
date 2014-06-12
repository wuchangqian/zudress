<?php
/**
 * CommerceLab Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the CommerceLab License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://commerce-lab.com/LICENSE.txt
 *
 * @category   CommerceLab
 * @package    CommerceLab_News
 * @copyright  Copyright (c) 2011 CommerceLab Co. (http://commerce-lab.com)
 * @license    http://commerce-lab.com/LICENSE.txt
 */

class CommerceLab_News_Model_Mysql4_News extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct(){
        $this->_init('clnews/news', 'news_id');
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('news_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('news_store'), $condition);

        //print_r((array)$object->getData('stores'));
        //die;
        if (count($object->getData('stores')) && (!in_array(0, (array)$object->getData('stores')))) {
            foreach ((array)$object->getData('stores') as $store) {
                $data = array();
                $data['news_id'] = $object->getId();
                $data['store_id'] = $store;
                $this->_getWriteAdapter()->insert($this->getTable('news_store'), $data);
            }
        } else {
            $data = array();
            $data['news_id'] = $object->getId();
            $data['store_id'] = '0';
            $this->_getWriteAdapter()->insert($this->getTable('news_store'), $data);
        }

        $condition = $this->_getWriteAdapter()->quoteInto('news_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('news_category'), $condition);

        foreach ((array)$object->getData('categories') as $category) {
            $data = array();
            $data['news_id'] = $object->getId();
            $data['category_id'] = $category;
            $this->_getWriteAdapter()->insert($this->getTable('news_category'), $data);
        }

        return parent::_afterSave($object);
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('news_store'))
            ->where('news_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $stores = array();
            foreach ($data as $row) {
                $stores[] = $row['store_id'];
            }
            $object->setData('store_id', $stores);
        }

        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('news_category'))
            ->where('news_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->fetchAll($select)) {
            $categories = array();
            foreach ($data as $row) {
                $categories[] = $row['category_id'];
            }
            $object->setData('category_id', $categories);
        }

        return parent::_afterLoad($object);
    }

    protected function _beforeDelete(Mage_Core_Model_Abstract $object){
        $adapter = $this->_getReadAdapter();
        $adapter->delete($this->getTable('clnews/news_category'), 'news_id='.$object->getId());
        $adapter->delete($this->getTable('clnews/comment'), 'news_id='.$object->getId());
    }
    /*
    protected function _getLoadSelect($field, $value, $object)
    {

        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getCategoryId()) {
            $select->join(array(
                        'category_store_table' => $this->getTable('category_store')),
                        $this->getMainTable().'.category_id = category_store_table.category_id')
                    ->where('category_store_table.store_id in (0, ?) ', Mage::app()->getStore()->getId());
        }
        return $select;
    }*/
}
