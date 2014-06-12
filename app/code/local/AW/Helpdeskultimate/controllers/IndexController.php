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

class AW_Helpdeskultimate_IndexController extends Mage_Adminhtml_Controller_Action
{
    const XML_PATH_EMAIL_CUSTOMER_TEMPLATE = 'helpdeskultimate/email/to_customer_email';
    const XML_PATH_EMAIL_SENDER = 'helpdeskultimate/email/sender_email';

    protected function _isAllowed()
    {
        $allowedCondition = Mage::getSingleton('admin/session')->isAllowed('helpdeskultimate/index');
        if (in_array($this->getRequest()->getActionName(), array('delete', 'customStatus'))) {
            $role = Mage::getSingleton('admin/session')->getUser()->getRole();
            $departmentPermissions = Mage::getModel('helpdeskultimate/department_permissions')
                ->loadByRoleId($role->getId());
            $id = $this->getRequest()->getParam('id', 0);
            $ticket = Mage::getModel('helpdeskultimate/ticket')->load($id);
            if (!is_null($departmentPermissions->getId()) && !is_null($ticket->getId())) {
                $allowedCondition = in_array($ticket->getData('department_id'), $departmentPermissions->getValue());
            }
        }
        return $allowedCondition;
    }

    protected function _initAction()
    {
        $this->_title($this->__('Help Desk - Tickets'));
        $this->loadLayout()
                ->_setActiveMenu('helpdeskultimate')
                ->_addBreadcrumb($this->__('Items Manager'), $this->__('Item Manager'));

        return $this;
    }

    public function indexAction()
    {
        $deparpments = Mage::getModel('helpdeskultimate/department')->getCollection()->addActiveFilter();
        if (!count($deparpments)) {
            Mage::getSingleton('adminhtml/session')->addError(
                $this->__(
                    'You have no departments configured yet. Please configure at least one active department <a href="%s">here</a>',
                    Mage::helper('adminhtml')->getUrl('helpdeskultimate_admin/departments/index')
                )
            );
        }

        $block = $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets');
        $grid = $block->getChild('grid');

        if (!is_null($depId = $this->getRequest()->getParam('department'))) {
            $grid->setDefaultFilter(array(
                'status' => $this->getRequest()->getParam('status'),
                'department_id' => $depId,
            ));
            $grid->setSaveParametersInSession(false);
        }

        $this->_initAction()
                ->_addContent($block)
                ->renderLayout();
    }

    public function ticketsAction()
    {
        $this->_initAction()
                ->renderLayout();
    }


    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('helpdeskultimate/ticket');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Item was successfully deleted'));
                $this->_redirect($this->_getRedirectPath(), $this->_getRedirectParams());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirectReferer();
            }
        }
        $this->_redirect($this->_getRedirectPath(), $this->_getRedirectParams());
    }

    public function statusOpenAction()
    {
        return $this->_changeStatus(AW_Helpdeskultimate_Model_Status::STATUS_OPEN);
    }

    public function statusClosedAction()
    {
        return $this->_changeStatus(AW_Helpdeskultimate_Model_Status::STATUS_CLOSED);
    }

    public function statusWaitingAction()
    {
        return $this->_changeStatus(AW_Helpdeskultimate_Model_Status::STATUS_WAITING);
    }

    public function customStatusAction()
    {
        if ($this->getRequest()->getParam('csid') > 0) {
            return $this->_changeStatus($this->getRequest()->getParam('csid'));
        }
        $this->_redirect($this->_getRedirectPath(), $this->_getRedirectParams());
    }

    public function massDeleteAction()
    {
        $tickets = $this->getRequest()->getParam('tickets');
        if (!is_array($tickets)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($tickets as $id) {
                    $ticket = Mage::getModel('helpdeskultimate/ticket')->load($id);
                    $ticket->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($tickets))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect($this->_getRedirectPath(), $this->_getRedirectParams());
    }


    public function massStatusAction()
    {
        $ids = $this->getRequest()->getParam('tickets');
        $status = $this->getRequest()->getParam('status');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                $updated = 0;
                $dissAllowed = array();
                foreach ($ids as $id) {
                    $ticket = Mage::getSingleton('helpdeskultimate/ticket')->load($id);

                    if (Mage::getSingleton('helpdeskultimate/status')->isAllowToSet($ticket->getStoreId(), $status)) {
                        $ticket
                                ->setStatus($this->getRequest()->getParam('status'))
                                ->setIsMassupdate(true)
                                ->save();
                        $updated++;
                    } else {
                        $dissAllowed[] = $ticket->getUid();
                    }
                }
                if ($updated) {
                    $this->_getSession()->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d record(s) were successfully updated', $updated)
                    );
                }
                if (count($dissAllowed)) {

                    $this->_getSession()->addError(
                        $this->__(
                            'Ticket(s) "%s" cannot be setted to status "%s"',
                            implode('", "', $dissAllowed),
                            Mage::getSingleton('helpdeskultimate/status')->getStatusLabel($status)
                        )
                    );
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect($this->_getRedirectPath(), $this->_getRedirectParams());
    }

    public function massLockAction()
    {
        $ids = $this->getRequest()->getParam('tickets');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $ticket = Mage::getSingleton('helpdeskultimate/ticket')
                            ->load($id);
                    if ($this->getRequest()->getParam('locked')) {
                        $lockId = Mage::getSingleton('admin/session')->getUser()->getId();
                    } else {
                        $lockId = 0;
                    }
                    $ticket->setLockedBy($lockId)
                        ->setLockedAt(now())
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($ids))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect($this->_getRedirectPath(), $this->_getRedirectParams());
    }

    public function massAssignAction()
    {
        $ids = $this->getRequest()->getParam('tickets');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {

                foreach ($ids as $id) {
                    $ticket = Mage::getSingleton('helpdeskultimate/ticket')
                            ->load($id);

                    if ($departmentId = $this->getRequest()->getParam('assign')) {
                        $ticket->setDepartmentId($departmentId);

                        # Check if department is changed and content is not
                        if (($ticket->getOrigData('department_id') != $ticket->getDepartmentId())) {
                            Mage::helper('helpdeskultimate/notify')
                                    ->ticketReassigned($ticket);
                        }
                    }
                    $ticket->setIsMassupdate(true)->save();
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($ids))
                );

            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect($this->_getRedirectPath(), $this->_getRedirectParams());
    }

    /**
     * Retrives redirect path for massactions
     * @return string
     */
    protected function _getRedirectPath()
    {
        $ret = $this->getRequest()->getParam('ret');
        if ($ret) {
            $backpath = $ret;
            $action = $this->getRequest()->getParam('action', 'edit');
            return '*/' . $backpath . '/' . $action;
        } else {
            return '*/*/';
        }
    }

    /**
     * Retrives redirect params
     * @return array
     */
    protected function _getRedirectParams()
    {
        $store = $this->getRequest()->getParam('store');
        $filter = $this->getRequest()->getParam('filter');
        $params = array();
        if ($store !== null) {
            $params['store'] = $store;
        }
        if ($filter) {
            $params['filter'] = $filter;
        }
        $ret = $this->getRequest()->getParam('ret');
        if ($ret) {
            $fieldname = $this->getRequest()->getParam('fieldname', 'id');
            $touch = $this->getRequest()->getParam('touch');
            $params[$fieldname] = $touch;
        }
        return $params;
    }

    public function exportCsvAction()
    {
        $fileName = 'helpdeskultimate.csv';
        $content = $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'helpdeskultimate.xml';
        $content = $this->getLayout()->createBlock('helpdeskultimate/adminhtml_tickets_grid')
                ->getXml();
        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    private function _changeStatus($status)
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('helpdeskultimate/ticket')->load($this->getRequest()->getParam('id'));
                if ($model->getId()) {
                    $model
                            ->setStatus($status)
                            ->save();
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Status was successfully changed'));
                }
                $this->_redirect($this->_getRedirectPath(), $this->_getRedirectParams());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirectReferer();
            }
        }
        $this->_redirect($this->_getRedirectPath(), $this->_getRedirectParams());
    }

    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('helpdeskultimate')->checkVersion('1.4.0.0')) {
            return parent::_title($text, $resetIfExists);
        }
        return $this;
    }

}
