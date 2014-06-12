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

class CommerceLab_News_Block_News extends Mage_Core_Block_Template
{
    protected $_pagesCount = null;
    protected $_currentPage = null;
    protected $_itemsOnPage = 10;
    protected $_pages;
    protected $_latestItemsCount = 2;

    protected function _construct()
    {
        $this->_currentPage = $this->getRequest()->getParam('page');
        if (!$this->_currentPage) {
            $this->_currentPage=1;
        }

        $itemsPerPage = (int)Mage::getStoreConfig('clnews/news/itemsperpage');
        if ($itemsPerPage > 0) {
            $this->_itemsOnPage = $itemsPerPage;
        }

        $latestItemsCount = (int)Mage::getStoreConfig('clnews/news/latestitemscount');
        if ($latestItemsCount > 0) {
            $this->_latestItemsCount = $latestItemsCount;
        }
    }

    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            // show breadcrumbs
            $moduleName = $this->getRequest()->getModuleName();
            $showBreadcrumbs = (int)Mage::getStoreConfig('clnews/news/showbreadcrumbs');
            if ($showBreadcrumbs && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) && ($moduleName=='clnews')) {
                $breadcrumbs->addCrumb('home',
                    array(
                    'label'=>Mage::helper('clnews')->__('Home'),
                    'title'=>Mage::helper('clnews')->__('Go to Home Page'),
                    'link'=> Mage::getBaseUrl()));
                $newsBreadCrumb = array(
                    'label'=>Mage::helper('clnews')->__(Mage::getStoreConfig('clnews/news/title')),
                    'title'=>Mage::helper('clnews')->__('Return to ' .Mage::helper('clnews')->__('News')),
                    );
                if ($this->getCategoryKey()) {
                    $newsBreadCrumb['link'] = Mage::getUrl($this->getAlias());
                }
                $breadcrumbs->addCrumb('news', $newsBreadCrumb);

                if ($this->getCategoryKey()) {
                    $categories = Mage::getModel('clnews/category')
                        ->getCollection()
                        ->addFieldToFilter('url_key', $this->getCategoryKey())
                        ->setPageSize(1);
                    $category = $categories->getFirstItem();
                    $breadcrumbs->addCrumb('category',
                        array(
                        'label'=>$category->getTitle(),
                        'title'=>Mage::helper('clnews')->__('Go to Home Page'),
                        ));
                }
            }
            // set default meta data
            $head->setTitle(Mage::getStoreConfig('clnews/news/metatitle'));
            $head->setKeywords(Mage::getStoreConfig('clnews/news/metakeywords'));
            $head->setDescription(Mage::getStoreConfig('clnews/news/metadescription'));

            // set category meta data if defined
            $currentCategory = $this->getCurrentCategory();
            if ($currentCategory!=null) {
                if ($currentCategory->getTitle()!='') {
                    $head->setTitle($currentCategory->getTitle());
                }
                if ($currentCategory->getMetaKeywords()!='') {
                    $head->setKeywords($currentCategory->getMetaKeywords());
                }
                if ($currentCategory->getMetaDescription()!='') {
                    $head->setDescription($currentCategory->getMetaDescription());
                }
            }
        }
    }

    public function getCategoryKey()
    {
        return $this->getRequest()->getParam('category');
    }

    public function getNewsItems()
    {
        $collection = Mage::getModel('clnews/news')->getCollection();

        if ($category = $this->getRequest()->getParam('category')) {
            /*$collection
                ->addCategoryFilter($category);
            $this->setCategory($category);*/
            $catCollection = Mage::getModel('clnews/category')->getCollection()
                ->addFieldToFilter('url_key', $category)
                ->addStoreFilter(Mage::app()->getStore()->getId());
            $categoryId = $catCollection->getData();
            if ($categoryId[0]['category_id']) {
                $tableName = Mage::getSingleton('core/resource')->getTableName('clnews_news_category');
                $collection->getSelect()->join($tableName, 'main_table.news_id = ' . $tableName . '.news_id','category_id');
                $collection->getSelect()->where($tableName . '.category_id =?', $categoryId[0]['category_id']);
            }
        } else {
            $collection->addStoreFilter(Mage::app()->getStore()->getId());
        }
        if ($tag = $this->getRequest()->getParam('q')) {
            $collection = Mage::getModel('clnews/news')->getCollection()->setOrder('news_time', 'desc');
            if (count(Mage::app()->getStores()) > 1) {
                $tableName = Mage::getSingleton('core/resource')->getTableName('clnews_news_store');
                $collection->getSelect()->join($tableName, 'main_table.news_id = ' . $tableName . '.news_id','store_id');
                $collection->getSelect()->where($tableName . '.store_id =?', Mage::app()->getStore()->getId());
            }
            $tag = urldecode($tag);
            $collection->getSelect()->where("tags LIKE '%". $tag . "%'");
        }

        $collection
            ->addEnableFilter(1)
            ->addFieldToFilter('publicate_from_time', array('or' => array(
                0 => array('date' => true, 'to' => date('Y-m-d H:i:s')),
                1 => array('is' => new Zend_Db_Expr('null'))),
                ), 'left')
            ->addFieldToFilter('publicate_to_time', array('or' => array(
                0 => array('date' => true, 'from' => date('Y-m-d H:i:s')),
                1 => array('is' => new Zend_Db_Expr('null'))),
                ), 'left')
            ->setOrder('news_time ', 'desc');
        $this->_pagesCount = ceil($collection->getSize()/$this->_itemsOnPage);
        for ($i=1; $i<=$this->_pagesCount;$i++) {
            $this->_pages[] = $i;
        }
        $this->setLastPageNum($this->_pagesCount);

        $collection->setPageSize($this->_itemsOnPage);
        $collection->setCurPage($this->_currentPage);

        foreach ($collection as $item) {
            $comments = Mage::getModel('clnews/comment')->getCollection()
                ->addNewsFilter($item->getNewsId())
                ->addApproveFilter(CommerceLab_News_Helper_Data::APPROVED_STATUS);
            $item->setCommentsCount(count($comments));
        }
        return $collection;
    }

    public function getLatestNewsItems()
    {
        $collection = Mage::getModel('clnews/news')->getCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId());
        $collection->setPageSize($this->_latestItemsCount);
        $collection
            ->addEnableFilter(1)
            ->addFieldToFilter('publicate_from_time', array('or' => array(
                0 => array('date' => true, 'to' => date('Y-m-d H:i:s')),
                1 => array('is' => new Zend_Db_Expr('null'))),
                ), 'left')
            ->addFieldToFilter('publicate_to_time', array('or' => array(
                0 => array('date' => true, 'from' => date('Y-m-d H:i:s')),
                1 => array('is' => new Zend_Db_Expr('null'))),
                ), 'left')
            ->setOrder('news_time ', 'desc');
        return $collection;
    }

    public function getCategories()
    {
        $collection = Mage::getModel('clnews/category')->getCollection()
        ->addStoreFilter(Mage::app()->getStore()->getId())
        ->setOrder('sort_order ', 'asc');

        foreach ($collection as $item) {
            $item->setLink(Mage::getBaseUrl().$this->getAlias().'/category/'.$item->getUrlKey().'.html');
        }
        return $collection;
    }

    public function getTopLink()
    {
        $route = Mage::helper('clnews')->getRoute();
        $title = Mage::helper('clnews')->__(Mage::getStoreConfig('clnews/news/title'));
        $this->getParentBlock()->addLink($title, $route, $title, true, array(), 15, null, 'class="top-link-news"');
    }

    public function getItemUrl($itemId) {
        return $this->getUrl($this->getAlias().'/newsitem/view', array('id' => $itemId));
    }

    public function isFirstPage()
    {
        if ($this->_currentPage==1) {
            return true;
        }
        return false;
    }

    public function isLastPage()
    {
        if ($this->_currentPage==$this->_pagesCount) {
            return true;
        }
        return false;
    }

    public function isPageCurrent($page)
    {
        if ($page==$this->_currentPage) {
            return true;
        }
        return false;
    }

    public function getPageUrl($page)
    {
        if ($category = $this->getRequest()->getParam('category')) {
            return $this->getUrl('*', array('category' => $category, 'page' => $page));
        } else {
            return $this->getUrl('*', array('page' => $page));
        }
    }

    public function getNextPageUrl()
    {
        $page = $this->_currentPage+1;
        return $this->getPageUrl($page);
    }

    public function getPreviousPageUrl()
    {
        $page = $this->_currentPage-1;
        return $this->getPageUrl($page);
    }

    public function getPages()
    {
        return $this->_pages;
    }

    public function getAlias()
    {
        return Mage::helper('clnews')->getRoute();
    }

    public function getCurrentCategory()
    {
        if ($this->getCategoryKey()) {
            $categories = Mage::getModel('clnews/category')
                ->getCollection()
                ->addFieldToFilter('url_key', $this->getCategoryKey())
                ->setPageSize(1);
            $category = $categories->getFirstItem();
            return $category;
        }
        return null;
    }
}
