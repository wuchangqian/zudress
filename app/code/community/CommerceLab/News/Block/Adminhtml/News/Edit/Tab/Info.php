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

class CommerceLab_News_Block_Adminhtml_News_Edit_Tab_Info extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('news_form', array('legend'=>Mage::helper('clnews')->__('News information')));

        $fieldset->addField('status', 'select', array(
        'label'     => Mage::helper('clnews')->__('Status'),
        'name'      => 'status',
        'values'    => array(
          array(
              'value'     => 1,
              'label'     => Mage::helper('clnews')->__('Enabled'),
          ),

          array(
              'value'     => 2,
              'label'     => Mage::helper('clnews')->__('Disabled'),
          ),
        ),
        ));

        $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('clnews')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
        ));

        $fieldset->addField('url_key', 'text', array(
          'label'     => Mage::helper('clnews')->__('URL Key'),
          'title'     => Mage::helper('clnews')->__('URL Key'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'url_key',
          'class'     => 'validate-identifier',
          'after_element_html' => '<div class="hint"><p class="note">e.g. domain.com/news/url_key</p></div>',
        ));

            /**
             * Check is single store mode
             */
        if (!Mage::app()->isSingleStoreMode()) {
                $fieldset->addField('store_id', 'multiselect', array(
                    'name'      => 'stores[]',
                    'label'     => Mage::helper('clnews')->__('Store View'),
                    'title'     => Mage::helper('clnews')->__('Store View'),
                    'required'  => true,
                    'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }
        else {
                $fieldset->addField('store_id', 'hidden', array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                ));
        }

        $categories = array();
        $collection = Mage::getModel('clnews/category')->getCollection()->setOrder('sort_order', 'asc');
        foreach ($collection as $cat) {
            $categories[] = ( array(
                'label' => (string)$cat->getTitle(),
                'value' => $cat->getCategoryId()
                ));
        }
            $fieldset->addField('category_id', 'multiselect', array(
                    'name'      => 'categories[]',
                    'label'     => Mage::helper('clnews')->__('Category'),
                    'title'     => Mage::helper('clnews')->__('Category'),
                    'required'  => false,
                    'style'     => 'height:100px',
                    'values'    => $categories,
            ));

        $fieldset->addField('comments_enabled', 'select', array(
        'label'     => Mage::helper('clnews')->__('Comments'),
        'name'      => 'comments_enabled',
        'values'    => array(
          array(
              'value'     => 1,
              'label'     => Mage::helper('clnews')->__('Enabled'),
          ),

          array(
              'value'     => 0,
              'label'     => Mage::helper('clnews')->__('Disabled'),
          ),
        ),
        ));
        $newsCollection = Mage::getModel('clnews/news')->getCollection()
                            ->addFieldToFilter('news_id', $this->getRequest()->getParam('id'));
        $fieldset->addField('document_save', 'file', array(
            'label'     => Mage::helper('clnews')->__('File'),
            'required'  => false,
            'name'      => 'document_save',
        ));
        $file = $newsCollection->getData();
        if ($this->getRequest()->getParam('id')) {
            $documents = $file[0]['document'];
            $full_path = $file[0]['full_path_document'];
            $tag = $file[0]['tags'];
        } else {
            $documents = NULL;
            $full_path = '';
            $tag = '';
        }
        if ($documents) {
            $fieldset->addField('is_delete', 'checkbox', array(
                'name'      => 'is_delete',
                'label'     => Mage::helper('clnews')->__('Delete File'),
                'after_element_html' => '<a href="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'clnews' . DS . $documents . '" >' . $documents. '</a>',
            ));

            $fieldset->addField('full_path_document', 'hidden', array(
                'name'       => 'full_path_document',
                'value'      => $full_path,
            ));
        }

        $fieldset->addField('link', 'text', array(
            'label'     => Mage::helper('clnews')->__('Link Name'),
            'title'     => Mage::helper('clnews')->__('Link Name'),
            'name'      => 'link',
            'after_element_html' => '<div class="hint"><p class="note">e.g. Download attached document - default</p></div>',
        ));

        $fieldset->addField('tags', 'text', array(
              'label'     => Mage::helper('clnews')->__('Add Tags'),
              'title'     => Mage::helper('clnews')->__('Add Tags'),
              'name'      => 'tags',
              'value'     => $tag,
              'after_element_html' => '<div class="hint"><p class="note">Use comma for multiple words</p></div>',
            ));

        $fieldset->addField('use_full_img', 'checkbox', array(
            'label'     => Mage::helper('clnews')->__('Use Full Description Image'),
            'required'  => false,
            'name'      => 'use_full_img',
            'checked'   => 'checked',
            'onclick'   => 'checkboxSwitch();'
        ));

        $fieldset->addField('image_short_content', 'image', array(
            'label'     => Mage::helper('clnews')->__('Image for Short Description'),
            'required'  => false,
            'name'      => 'image_short_content',
            'after_element_html' => '<script type="text/javascript">
                                            if (!document.getElementById("use_full_img").checked) {
                                                document.getElementById("image_short_content").disabled=0;
                                            } else {
                                                document.getElementById("image_short_content").disabled=1;
                                            }

                                        function checkboxSwitch(){
                                            if (!document.getElementById("use_full_img").checked) {
                                                document.getElementById("image_short_content").disabled=0;
                                            } else {
                                                document.getElementById("image_short_content").disabled=1;
                                            }
                                        };
                                    </script>',
        ));

        $fieldset->addField('short_height_resize', 'text', array(
              'label'     => Mage::helper('clnews')->__('Resize Image Height'),
              'title'     => Mage::helper('clnews')->__('Resize Image Height'),
              'name'      => 'short_height_resize',
              'style'     => 'width: 50px;',
              'after_element_html' => '<span class="hint">px</span>',
            ));

        $fieldset->addField('short_width_resize', 'text', array(
              'label'     => Mage::helper('clnews')->__('Resize Image Width'),
              'title'     => Mage::helper('clnews')->__('Resize Image Width'),
              'name'      => 'short_width_resize',
              'style'     => 'width: 50px;',
              'after_element_html' => '<span class="hint">px</span>',
            ));

        $fieldset->addField('image_short_content_show', 'select', array(
            'label'     => Mage::helper('clnews')->__('Show Image'),
            'required'  => false,
            'name'      => 'image_short_content_show',
            'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('clnews')->__('Yes'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('clnews')->__('No'),
              ),
            ),
        ));

        $fieldset->addField('short_content', 'editor', array(
            'name'      => 'short_content',
            'label'     => Mage::helper('clnews')->__('Short Description'),
            'title'     => Mage::helper('clnews')->__('Short Description'),
            'config'    => Mage::getSingleton('clnews/wysiwyg_config')->getConfig()
        ));

        $fieldset->addField('image_full_content', 'image', array(
            'label'     => Mage::helper('clnews')->__('Image for Full Description'),
            'required'  => false,
            'name'      => 'image_full_content',
        ));

        $fieldset->addField('full_height_resize', 'text', array(
              'label'     => Mage::helper('clnews')->__('Resize Image Height'),
              'title'     => Mage::helper('clnews')->__('Resize Image Height'),
              'name'      => 'full_height_resize',
              'style'     => 'width: 50px;',
              'after_element_html' => '<span class="hint">px</span>',
            ));

        $fieldset->addField('full_width_resize', 'text', array(
              'label'     => Mage::helper('clnews')->__('Resize Image Width'),
              'title'     => Mage::helper('clnews')->__('Resize Image Width'),
              'name'      => 'full_width_resize',
              'style'     => 'width: 50px;',
              'after_element_html' => '<span class="hint">px</span>',
            ));

        $fieldset->addField('image_full_content_show', 'select', array(
            'label'     => Mage::helper('clnews')->__('Show Image'),
            'required'  => false,
            'name'      => 'image_full_content_show',
            'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('clnews')->__('Yes'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('clnews')->__('No'),
              ),
            ),
        ));


        $fieldset->addField('full_content', 'editor', array(
            'name'      => 'full_content',
            'label'     => Mage::helper('clnews')->__('Full Description'),
            'title'     => Mage::helper('clnews')->__('Full Description'),
            'style'     => 'height:36em',
            'config'    => Mage::getSingleton('clnews/wysiwyg_config')->getConfig()
        ));


        if (Mage::getSingleton('adminhtml/session')->getNewsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getNewsData());
            Mage::getSingleton('adminhtml/session')->setNewsData(null);
        } elseif ( Mage::registry('clnews_data') ) {
            $form->setValues(Mage::registry('clnews_data')->getData());
        }
        $this->setForm($form);
        return $this;
    }
}
