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

class AW_Helpdeskultimate_TemplatesController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('helpdeskultimate/departments_templates');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Help Desk - Quick responses'));
        $this->loadLayout()
                ->_setActiveMenu('helpdeskultimate')
                ->_addBreadcrumb($this->__('Items Manager'), $this->__('Item Manager'));

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('helpdeskultimate/adminhtml_departments_templates'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }


    public function editAction()
    {
        $model = Mage::getModel('helpdeskultimate/template');
        if ($id = $this->getRequest()->getParam('id')) {
            if ($model->load($id)) {
                if ($model->getId()) {
                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Template does not exist'));
                    $this->_redirect('*/');
                }
            }
        }

        Mage::register('template', $model);
        $this->_initAction();
        $_title = is_null($model->getId()) ? $this->__('New Quick Response') : $model->getName();
        $this->_title($_title);
        $this
                ->_addContent($this->getLayout()->createBlock('helpdeskultimate/adminhtml_departments_templates_edit'));
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        $this->renderLayout();
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if (isset($data['id']) && !$data['id']) {
            unset($data['id']);
        }

        try {
            if ($data) {
                $model = Mage::getModel('helpdeskultimate/template')->setData($data);
            } else {
                throw(new Exception('No data to save transfered'));
            }

            if ($model->save()) {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Template was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                } else {
                    $this->_redirect('*/*/');
                    return;
                }
            } else {
                throw(new Exception('Template wasn\'t saved'));
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }

    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('helpdeskultimate/template');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Template was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirectReferer();
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('templates');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $item = Mage::getModel('helpdeskultimate/template')->load($id);
                    $item->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($ids))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }


    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('helpdeskultimate')->checkVersion('1.4.0.0')) {
            return parent::_title($text, $resetIfExists);
        }
        return $this;
    }
}