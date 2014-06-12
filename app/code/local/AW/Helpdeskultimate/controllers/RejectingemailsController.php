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

class AW_Helpdeskultimate_RejectingemailsController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        return $this->loadLayout()->_setActiveMenu('helpdeskultimate/rejecting');
    }

    protected function _getHelper($name = null)
    {
        return Mage::helper('helpdeskultimate' . ($name ? '/' . $name : ''));
    }

    /**
     * Returns true when admin session contain error messages
     */
    private function _hasErrors()
    {
        return (bool)count($this->_getSession()->getMessages()->getItemsByType('error'));
    }

    protected function indexAction()
    {
        $this->_redirect('*/*/patternslist');
    }

    protected function rejectedlistAction()
    {
        $this->_initAction();
        $this->_title($this->__('Rejected Emails List'));
        $this->renderLayout();
    }

    protected function patternslistAction()
    {
        $this->_initAction();
        $this->_title($this->__('Patterns List'));
        $this->renderLayout();
    }

    protected function newAction()
    {
        $this->_getHelper()->setRejectedFormData(array());
        return $this->_redirect('*/*/edit');
    }

    protected function editAction()
    {
        $this->_initAction();
        $ticketId = $this->getRequest()->getParam('id');
        $_formData = Mage::getModel('helpdeskultimate/rpattern')->load($ticketId);
        if (!$this->getRequest()->getParam('fswe') || !$this->_getHelper()->getRejectedFormData($ticketId)) {
            if ($_formData->getData()) {
                $this->_getHelper()->setRejectedFormData($_formData);
            }
            if (!$_formData->getData() && $this->getRequest()->getParam('id')) {
                $this->_getSession()->addError($this->__('Couldn\'t load pattern entry by given ID'));
                return $this->_redirect('*/*/patternslist');
            }
        }
        $_title = is_null($_formData->getId()) ? $this->__('Pattern') : $_formData->getName();
        $this->_title($this->__('Patterns List'))
            ->_title($_title);
        $this->renderLayout();
    }

    protected function patternsaveAction()
    {
        $_request = $this->getRequest();
        $_data = array('id' => $_request->getParam('id'));
        if ($_request->getParam('name')) {
            $_data['name'] = $_request->getParam('name');
            $_data['is_active'] = $_request->getParam('is_active') ? 1 : 0;
            if ($_request->getParam('scope')) {
                $_data['scope'] = $_request->getParam('scope');
                if ($_request->getParam('pattern')) {
                    $_data['pattern'] = $_request->getParam('pattern');
                    if (
                        preg_match("/\/.*\/[imsxADSUX]*$/", $_data['pattern'])
                        && !(@preg_match($_data['pattern'], '') === false)
                    ) {
                        if (!preg_match("/\/.*\/.*[m].*$/", $_data['pattern'])) {
                            $_data['pattern'] .= 'm';
                        }
                    } else {
                        $this->_getSession()->addError(
                            $this->__('Wrong pattern syntax. Allowed format: /.*/imsxADSUX')
                        );
                    }
                } else {
                    $this->_getSession()->addError($this->__('Pattern can\'t be empty'));
                }
            } else {
                $this->_getSession()->addError($this->__('Name can\'t be empty'));
            }
        } else {
            $this->_getSession()->addError($this->__('Name can\'t be empty'));
        }

        if ($this->_hasErrors()) {
            $this->_getHelper()->setRejectedFormData($_request->getParams());
            return $this->_redirect('*/*/edit', array('id' => $_request->getParam('id'), 'fswe' => 1));
        } else {
            $_pattern = Mage::getModel('helpdeskultimate/rpattern')->load($this->getRequest()->getParam('id'));
            $_pattern->setData($_data);
            $_pattern->save();

            $this->_getSession()->addSuccess($this->__('Pattern has been succesfully saved'));
            if ($this->getRequest()->getParam('continue'))
                return $this->_redirect('*/*/edit', array('id' => $_pattern->getId()));
            else
                return $this->_redirect('*/*/patternslist');
        }
    }

    protected function deleteAction()
    {
        $_pattern = Mage::getModel('helpdeskultimate/rpattern')->load($this->getRequest()->getParam('id'));
        if ($_pattern->getData()) {
            $_pattern->delete();
            $this->_getSession()->addNotice($this->__('Pattern has been successfully deleted'));
        }
        return $this->_redirect('*/*/patternslist');
    }

    protected function rejectedtounprocessedAction()
    {
        $popmessage = Mage::getModel('helpdeskultimate/popmessage')->load($this->getRequest()->getParam('id'));
        if ($popmessage->getData()) {
            $popmessage->setStatus(AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection::STATUS_UNPROCESSED)
                ->save();
            $this->_getSession()->addNotice($this->__('Email has been marked as unprocessed'));
        }
        return $this->_redirect('*/*/rejectedlist');
    }

    protected function rejectedtoprocessedAction()
    {
        $popmessage = Mage::getModel('helpdeskultimate/popmessage')->load($this->getRequest()->getParam('id'));
        if ($popmessage->getData()) {
            $popmessage->setStatus(AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection::STATUS_PROCESSED)
                ->save();
            $this->_getSession()->addNotice($this->__('Email has been deleted from rejected emails list'));
        }
        return $this->_redirect('*/*/rejectedlist');
    }

    protected function patternMassDeleteAction()
    {
        $patterns = $this->getRequest()->getParam('patterns');
        if (!is_array($patterns)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($patterns as $id) {
                    $rPattern = Mage::getModel('helpdeskultimate/rpattern')->load($id);
                    $rPattern->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($patterns))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        return $this->_redirect('*/*/patternslist');
    }

    protected function patternMassStatusAction()
    {
        $patterns = $this->getRequest()->getParam('patterns');
        $status = $this->getRequest()->getParam('status');
        if (!is_array($patterns)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($patterns as $id) {
                    $rPattern = Mage::getModel('helpdeskultimate/rpattern')->load($id);
                    $rPattern->setData('is_active', $status)->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were successfully updated', count($patterns))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        return $this->_redirect('*/*/patternslist');
    }


    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('helpdeskultimate')->checkVersion('1.4.0.0')) {
            return parent::_title($text, $resetIfExists);
        }
        return $this;
    }
}
