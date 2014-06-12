<?php

class WP_CustomMenu_Block_Navigation extends Mage_Catalog_Block_Navigation
{
    const CUSTOM_BLOCK_TEMPLATE = 'wp_custom_menu_%d';

	public function getIsHomePage()
    {
        return $this->getUrl('') == $this->getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));

		 // $routeName = Mage::app()->getRequest()->getRouteName();
		 // $identifier = Mage::getSingleton('cms/page')->getIdentifier();
 
		 // if($routeName == 'cms' && $identifier == 'home') {return true;}
    }

    public function showHomeLink()
    {
        return Mage::getStoreConfig('custom_menu/general/show_home_link');
    }

    public function drawCustomMenuItem($category, $level = 0, $last = false)
    {
        if (!$category->getIsActive()) return '';

        $html = array();

        $id = $category->getId();
        // --- Static Block ---
        $blockId = sprintf(self::CUSTOM_BLOCK_TEMPLATE, $id); // --- static block key
        $blockHtml = $this->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
        // --- Sub Categories ---
        $activeChildren = $this->getActiveChildren($category, $level);
        // --- class for active category ---
        $active = ''; if ($this->isCategoryActive($category)) $active = ' act';
        // --- Popup functions for show ---
        $drawPopup = ($blockHtml || count($activeChildren));
        if ($drawPopup)
        {
            $html[] = '<div id="menu' . $id . '" class="menu' . $active . ' top'. $this->_getItemPosition(0) . '" onmouseover="wppShowMenuPopup(this, \'popup' . $id . '\');" onmouseout="wppHideMenuPopup(this, event, \'popup' . $id . '\', \'menu' . $id . '\')">';
        }
        else
        {
            $html[] = '<div id="menu' . $id . '" class="menu' . $active . ' top'. $this->_getItemPosition(0) .'">';
        }
        // --- Top Menu Item ---
        if($activeChildren){
            $html[] = '<div class="parentMenu">';
        }else{ // 有子分类的情况
            $html[] = '<div class="pparentMenu">';
        }
        $html[] = '<a href="'.$this->getCategoryUrl($category).'">';
        $name = $this->escapeHtml($category->getName());
        if (Mage::getStoreConfig('custom_menu/general/non_breaking_space'))
            $name = str_replace(' ', '&nbsp;', $name);
        $html[] = '<span>' . $name . '</span>';
        $html[] = '</a>';
        $html[] = '</div>';
        $html[] = '</div>';
        // --- Add Popup block (hidden) ---

		$topheight=(int)$this->_getItemPosition(0)*27;

        if ($drawPopup)
        {
            // --- Popup function for hide ---
            $html[] = '<div id="popup' . $id . '"  class="wp-custom-menu-popup"   style="display:none;left:220px;margin-top:-27px;;"  onmouseout="wppHideMenuPopup(this, event, \'popup' . $id . '\', \'menu' . $id . '\')">';
            // --- draw Sub Categories ---
            if (count($activeChildren))
            {
                $html[] = '<div class="block1">';
                $html[] = $this->drawColumns($activeChildren);
				$html[] = '<div class="clearBoth"></div>';
				$html[] = '</div>';
            }
            // --- draw Custom User Block ---
            if ($blockHtml)
            {
                $html[] = '<div class="block2">';
                $html[] = $blockHtml;
				$html[] = '</div>';
               // $html[] = "$id $blockId";
            }
            $html[] = '</div>';
        }

        $html = implode("\n", $html);
        return $html;
    }

    public function drawColumns($children)
    {
        $html = '';
        // --- explode by columns ---
        $columns = (int)Mage::getStoreConfig('custom_menu/columns/count');
        if ($columns < 1) $columns = 1;
		
        $chunks = $this->explodeByColumns($children, $columns);
		
        // --- draw columns ---
        $lastColumnNumber = count($chunks);
		$popWidth = $lastColumnNumber * 210;
        $i = 1;
		$html.= '<div class="full_block" style ="width:'. $popWidth .'px">';
        foreach ($chunks as $key => $value)
        {
            if (!count($value)) continue;
            $class = '';
            if ($i == 1) $class.= ' first';
            if ($i == $lastColumnNumber) $class.= ' last';
            if ($i % 2) $class.= ' odd'; else $class.= ' even';
            $html.= '<div class="column' . $class . '">';
            $html.= $this->drawMenuItem($value, 1);
            $html.= '</div>';
            $i++;
        }
        $html.= '</div>';
        return $html;
    }

    protected function getActiveChildren($parent, $level)
    {
        $activeChildren = array();
        // --- check level ---
        $maxLevel = (int)Mage::getStoreConfig('custom_menu/general/max_level');
        if ($maxLevel > 0)
        {
            if ($level >= ($maxLevel - 1)) return $activeChildren;
        }
        // --- / check level ---
        if (Mage::helper('catalog/category_flat')->isEnabled())
        {
            $children = $parent->getChildrenNodes();
            $childrenCount = count($children);
        }
        else
        {
            $children = $parent->getChildren();
            $childrenCount = $children->count();
        }
        $hasChildren = $children && $childrenCount;
        if ($hasChildren)
        {
            foreach ($children as $child)
            {
                if ($child->getIsActive())
                {
                    array_push($activeChildren, $child);
                }
            }
        }
        return $activeChildren;
    }

    private function explodeByColumns($target, $num)
    {
        $count = count($target);
        if ($count) $target = array_chunk($target, ceil($count / $num));
        $target = array_pad($target, $num, array());
        #return $target;
        if ((int)Mage::getStoreConfig('custom_menu/columns/integrate') && count($target))
        {
            // --- combine consistently numerically small column ---
            // --- 1. calc length of each column ---
            $max = 0; $columnsLength = array();
            foreach ($target as $key => $child)
            {
                $count = 0;
                $this->_countChild($child, 1, $count);
                if ($max < $count) $max = $count;
                $columnsLength[$key] = $count;
            }
            // --- 2. merge small columns with next ---
            $xColumns = array(); $column = array(); $cnt = 0;
            $xColumnsLength = array(); $k = 0;
            foreach ($columnsLength as $key => $count)
            {
                $cnt+= $count;
                if ($cnt > $max && count($column))
                {
                    $xColumns[$k] = $column;
                    $xColumnsLength[$k] = $cnt - $count;
                    $k++; $column = array(); $cnt = $count;
                }
                $column = array_merge($column, $target[$key]);
            }
            $xColumns[$k] = $column;
            $xColumnsLength[$k] = $cnt - $count;
            // --- 3. integrate columns of one element ---
            $target = $xColumns; $xColumns = array(); $nextKey = -1;
            if ($max > 1 && count($target) > 1)
            {
                foreach($target as $key => $column)
                {
                    if ($key == $nextKey) continue;
                    if ($xColumnsLength[$key] == 1)
                    {
                        // --- merge with next column ---
                        $nextKey = $key + 1;
                        if (isset($target[$nextKey]) && count($target[$nextKey]))
                        {
                            $xColumns[] = array_merge($column, $target[$nextKey]);
                            continue;
                        }
                    }
                    $xColumns[] = $column;
                }
                $target = $xColumns;
            }
        }
        return $target;
    }

    private function _countChild($children, $level, &$count)
    {
        foreach ($children as $child)
        {
            if ($child->getIsActive())
            {
                $count++; $activeChildren = $this->getActiveChildren($child, $level);
                if (count($activeChildren) > 0) $this->_countChild($activeChildren, $level + 1, $count);
            }
        }
    }

    public function drawMenuItem($children, $level = 1)
    {
        $html = '<div class="itemMenu level' . $level . '">';
        $keyCurrent = $this->getCurrentCategory()->getId();
		$i=1;
        foreach ($children as $child)
        {
            if ($child->getIsActive())
            {  
                
				// --- class for active category ---
                $active = '';
                if ($this->isCategoryActive($child))
                {
                    $active = ' actParent';
                    if ($child->getId() == $keyCurrent) $active = ' act';
                }
                // --- format category name ---
                $name = $this->escapeHtml($child->getName());
                if (Mage::getStoreConfig('custom_menu/general/non_breaking_space'))
                    $name = str_replace(' ', '&nbsp;', $name);
                $html.=  '<a class="itemMenuName level' . $level . $active . ' item' .$i.'" href="' . $this->getCategoryUrl($child) . '"><span>' . $name . '</span></a>';
                $activeChildren = $this->getActiveChildren($child, $level);
                if (count($activeChildren) > 0)
                {
                    $html.= '<div class="itemSubMenu level' . $level . '">';
                    $html.= $this->drawMenuItem($activeChildren, $level + 1);
                    $html.= '</div>';
                }
				$i++;

            }
			
        }
        $html.= '</div>';
        return $html;
    }
}
