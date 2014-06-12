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

class AW_Helpdeskultimate_GatewaysController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('helpdeskultimate/gateways');
    }

    protected function _initAction()
    {
        $this->_title($this->__('Help Desk - Gateways'));
        $this->loadLayout()
                ->_setActiveMenu('helpdeskultimate');

        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('helpdeskultimate/adminhtml_gateways'))
                ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        try {
            $model = Mage::getModel('helpdeskultimate/gateway');
            if ($this->getRequest()->getParam('id')) {
                $model->load($this->getRequest()->getParam('id'));
                @$data['id'] = $this->getRequest()->getParam('id');
            }
            if ($data) {
                $model->setData($data);
            } else {
                throw(new Exception('No data to save transfered'));
            }
            if ($model->save()) {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Gateway was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);


                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                } else {
                    $this->_redirect('*/*/');
                    return;
                }
            } else {
                throw(new Exception('Gateway wasn\'t saved'));
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }

    }

    public function editAction()
    {
        $model = Mage::getModel('helpdeskultimate/gateway');
        if ($id = $this->getRequest()->getParam('id')) {
            if ($model->load($id)) {
                if ($model->getId()) {
                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Gateway does not exist'));
                    $this->_redirect('*/');
                }
            }
        }

        Mage::register('gateway', $model);

        $this->_initAction();
        $_title = is_null($model->getId()) ? $this->__('New Email Gateway') : $model->getTitle();
        $this->_title($_title);
        $this
                ->_addContent($this->getLayout()->createBlock('helpdeskultimate/adminhtml_gateways_edit'))
                ->renderLayout();
    }

    /**
     * Action to delete gateway
     * @return
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('helpdeskultimate/gateway');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Gateway was successfully deleted'));
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
        $ids = $this->getRequest()->getParam('gateways');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $item = Mage::getModel('helpdeskultimate/gateway')->load($id);
                    $item->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Total of %d record(s) were successfully deleted', count($ids))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Unparas params for Imap connection
     * @param string $params
     * @return Varien_Object
     */
    protected function _unparseParams($params)
    {
        $data = array();
        $params = base64_decode($params);
        parse_str($params, $data);
        $data=array_map("htmlspecialchars_decode", $data);
        return new Varien_Object($data);
    }

    /**
     * @return void
     */
    public function testconnectionAction()
    {
        $params = $this->getRequest()->getParam('params');
        if ($params) {
            $params = $this->_unparseParams($params);
        }

        $error = false;
        try {
            $connection = Mage::getModel('helpdeskultimate/gateway_connection')->initFromVarienObject($params);
            if ($connection === true) {
                $connectionResult = AW_Helpdeskultimate_Model_Form_Element_Testconnection::STATUS_SUCCESS;
            } else {
                $connectionResult = AW_Helpdeskultimate_Model_Form_Element_Testconnection::STATUS_FAIL;
            }

        } catch (Exception $e) {
            $connectionResult = AW_Helpdeskultimate_Model_Form_Element_Testconnection::STATUS_FAIL;
            $error = $e->getMessage();
        }
        $result = array('result' => $connectionResult, 'error' => $this->__($error));
        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('helpdeskultimate')->checkVersion('1.4.0.0')) {
            return parent::_title($text, $resetIfExists);
        }
        return $this;
    }
}
