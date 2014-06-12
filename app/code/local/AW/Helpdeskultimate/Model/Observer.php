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


class AW_Helpdeskultimate_Model_Observer
{

    /**
     * Detects if form submitted by bot or not
     * @return true [if bot]
     */
    protected function _isBot()
    {
        if (Mage::app()->getRequest()->getParam('fail_key')) {
            // Auth by fail key
            if ($lc = Mage::getSingleton('customer/session')->getLastBotCheck()) {
                if ((time() - $lc) < 15) {
                    Mage::getSingleton('customer/session')->setLastBotCheck(time());
                    return true;
                } else {
                    Mage::getSingleton('customer/session')->setLastBotCheck(time());
                }
            }
            $failKey = Mage::app()->getRequest()->getParam('fail_key');
            $failKeyHash = Mage::app()->getRequest()->getParam('fail_key_hash');
            return !Mage::getSingleton('helpdeskultimate/antibot')->checkFailKey($failKey, $failKeyHash);
        } else {
            $hduSeed = Mage::app()->getRequest()->getParam('hdu_seed');
            return !Mage::getSingleton('helpdeskultimate/antibot')->checkSeed($hduSeed);
        }
    }

    /**
     * Checks and creates proto from contact form
     * @return
     */
    public function contactFormBotProtect($observer)
    {
        $request = Mage::app()->getRequest();
        $response = Mage::app()->getResponse();
        $_configHelper = Mage::helper('helpdeskultimate/config');
        if ($request->getParam('antibot-field', false) && $_configHelper->isIntegrationWithContactFormEnabled()) {
            $this->saveContactFormToTicket();
            if ($_configHelper->isStandartContactFormDisabled()) {
                $successMessage = Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.');
                Mage::getSingleton('customer/session')->addSuccess($successMessage);
                $response->setRedirect(Mage::getUrl('*/*/'))->sendResponse();
                die();
            }
        } else {
            $errorMessage = Mage::helper('helpdeskultimate')->__('Antispam protection failed');
            Mage::getSingleton('core/session')->addError($errorMessage);
            $observer->getControllerAction()->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $myController = new AW_Helpdeskultimate_Controller_Action($request, $response);
            $myController->redirectReferer(Mage::getUrl('contacts/index/index'));
            return;
        }
    }

    /**
     * Creates ticket from order
     * @return
     */
    public function createFromOrder()
    {
        // Create proto
        if (Mage::app()->getRequest()->getParam('create_ticket')) {
            $order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));

            $history = Mage::app()->getRequest()->getPost();
            $history = @$history['history'];

            $proto = Mage::getModel('helpdeskultimate/proto')
                ->setSubject(Mage::helper('helpdeskultimate')->__('Order #%s', $order->getIncrementId()))
                ->setContent(trim(@$history['comment']))
                ->setContentType('text/plain')
                ->setFrom($order->getCustomerEmail())
                ->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PENDING)
                ->setStoreId($order->getStoreId())
                ->setSource('order')
                ->setCreatedBy(AW_Helpdeskultimate_Model_Ticket::CREATED_BY_ADMIN)
                ->setOrderId($order->getId());
            try {
                $proto->save();
                if ($proto->canBeConvertedToMessage()) {
                    $proto->convertToMessage();
                } else {
                    $proto->convertToTicket();
                }
                $proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PROCESSED)->save();
            }
            catch (Exception $e) {
                $this->log("Error occuring when create ticket from order: {$e->getMessage()}");
                $proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_FAILED)->save();
            }
        }
    }

    /**
     * Checks and creates proto from pq form
     * @return
     */
    public function PQFormBotProtect()
    {
        $_configHelper = Mage::helper('helpdeskultimate/config');
        if ($_configHelper->isIntegrationWithPQEnabled()) {
            $this->savePQFormToTicket();
        }
    }


    /**
     * Saves contact form to ticket proto
     * @return
     */
    public function saveContactFormToTicket()
    {
        $post = Mage::app()->getRequest()->getPost();

        if ($post) {
            foreach ($post as $k => $v) {
                if (is_string($v)) {
                    $post[$k] = strip_tags($post[$k]);
                }
            }

            try {
                $error = false;
                if (!Zend_Validate::is(trim(@$post['name']), 'NotEmpty')) {
                    $error = true;
                }
                if (!Zend_Validate::is(trim(@$post['comment']), 'NotEmpty')) {
                    $error = true;
                }
                if (!Zend_Validate::is(trim(@$post['email']), 'EmailAddress')) {
                    $error = true;
                }
                if ($departmentId = Mage::app()->getRequest()->getParam('department_id')) {
                    $department = Mage::getModel('helpdeskultimate/department')->load($departmentId);
                    if (!$department->getId()) {
                        $departmentId = null;
                        $error = true;
                    }
                    unset($department);
                }
                if ($error) {
                    throw new Exception();
                }
            } catch (Exception $e) {
                return;
            }

            if (isset($post['telephone'])) {
                $telephoneLine = "\r\n" . Mage::helper('helpdeskultimate')->__('Telephone:') . $post['telephone'];
                if (isset($post['comment'])) {
                    $post['comment'] .= $telephoneLine;
                } else {
                    $post['comment'] = $telephoneLine;
                }
            }

            $ticketSubject = Mage::helper('helpdeskultimate')->__(
                'Contact form %s <%s>',
                trim(isset($post['name']) ? $post['name'] : ''),
                trim(isset($post['email']) ? $post['email'] : '')
            );

            // Create proto
            $proto = Mage::getModel('helpdeskultimate/proto')
                ->setSubject($ticketSubject)
                ->setContent(trim(@$post['comment']))
                ->setContentType('text/plain')
                ->setDepartmentId($departmentId)
                ->setFrom(Mage::helper('helpdeskultimate')->__('%s <%s>', trim(@$post['name']), trim(@$post['email'])))
                ->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PENDING)
                ->setStoreId(Mage::app()->getStore()->getId())
                ->setSource('contacts');
            try {
                $proto->save();
                if ($proto->canBeConvertedToMessage()) {
                    $proto->convertToMessage();
                } else {
                    $proto->convertToTicket();
                }
                $proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PROCESSED)->save();
            }
            catch (Exception $e) {
                $this->log("Error occuring when create ticket from contact form: {$e->getMessage()}");
                $proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_FAILED)->save();
            }
        }
    }

    /**
     * Saves PQ form to ticket
     * @return
     */
    public function savePQFormToTicket()
    {
        $post = Mage::app()->getRequest()->getPost();

        if ($post) {
            foreach ($post as $k => $v) {
                $post[$k] = html_entity_decode(
                    $v,
                    ENT_QUOTES,
                    AW_Helpdeskultimate_Model_Data_Parser_Abstract::STORAGE_ENCODING
                );
            }
            try {
                $error = false;
                if (!Zend_Validate::is(trim(@$post['question_author_name']), 'NotEmpty')) {
                    $error = true;
                }
                if (!Zend_Validate::is(trim(@$post['question_text']), 'NotEmpty')) {
                    $error = true;
                }
                if (!Zend_Validate::is(trim(@$post['question_author_email']), 'EmailAddress')) {
                    $error = true;
                }
                if ($error) {
                    throw new Exception();
                }
            } catch (Exception $e) {
                return;
            }
            $productName = "";
            if (Mage::app()->getRequest()->getParam('id')) {
                $product = Mage::getModel('catalog/product')->load(Mage::app()->getRequest()->getParam('id'));
                $productName = $product->getName();
            }

            $ticketSubject = Mage::helper('helpdeskultimate')->__(
                'Product question on product %s from %s <%s>',
                $productName,
                trim(isset($post['question_author_name']) ? $post['question_author_name'] : ''),
                trim(isset($post['question_author_email']) ? $post['question_author_email'] : '')
            );

            // Create proto
            $proto = Mage::getModel('helpdeskultimate/proto')
                ->setSubject($ticketSubject)
                ->setContent(trim(@$post['question_text']))
                ->setContentType('text/plain')
                ->setFrom(trim(@$post['question_author_email']))
                ->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PENDING)
                ->setStoreId(Mage::app()->getStore()->getId())
                ->setSource('pq');

            try {
                $proto->save();
                if ($proto->canBeConvertedToMessage()) {
                    $proto->convertToMessage();
                } else {
                    $proto->convertToTicket();
                }
                $proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_PROCESSED)->save();
            }
            catch (Exception $e) {
                $this->log("Error occuring when create ticket from PQ: {$e->getMessage()}");
                $proto->setStatus(AW_Helpdeskultimate_Model_Proto::STATUS_FAILED)->save();
            }
        }
    }

    /**
     * If customer is deleted - close ticket and unassign any customer from ticket
     * @param object $event
     * @return
     */
    public function unlinkCustomerTickets($event)
    {
        $customer = $event->getCustomer();
        $ticketCollection = Mage::getModel('helpdeskultimate/ticket')
            ->getCollection()
            ->addCustomerFilter($customer->getId());
        foreach ($ticketCollection as $ticket) {
            $ticket
                ->setStatus(AW_Helpdeskultimate_Model_Status::STATUS_CLOSED)
                ->setCustomerId(0)->save();
        }
    }

    public function log($message)
    {
        Mage::helper('awcore/logger')->log($this, 'Observer', null, $message);
        return $this;
    }

    public function layoutRenderBefore($observer)
    {
        if (Mage::app()->getFrontController()->getAction() instanceof AW_Helpdeskultimate_CustomerController) {
            $_storeSwitcherBlock = Mage::getSingleton('core/layout')->getBlock('store_switcher');
            if ($_storeSwitcherBlock && ($_storeSwitcherBlock instanceof Mage_Page_Block_Switch)) {
                $_groups = $_storeSwitcherBlock->getGroups();
                $localeCode = Mage::getStoreConfig('general/locale/code');
                foreach ($_groups as $group) {
                    $store = $group->getDefaultStoreByLocale($localeCode);
                    if ($store && $group->getHomeUrl()) {
                        $group->setHomeUrl($store->getUrl('*/*/*', Mage::app()->getRequest()->getParams()));
                    }
                }
            }
        }
    }

    public function adminPermissionsRolePrepareSave($observer)
    {
        $value = $observer->getRequest()->getParam('allowed_departments');
        Mage::register('aw_hdu_allowed_department_value', $value);
    }

    public function adminRolesSaveAfter($observer)
    {
        $object = $observer->getDataObject();
        $roleId = $object->getId();
        $value = Mage::registry('aw_hdu_allowed_department_value');
        $departmentPermissions = Mage::getModel('helpdeskultimate/department_permissions')->loadByRoleId($roleId);
        if (is_null($roleId)) {
            return;
        }

        $departmentPermissions->addData(array(
            'role_id' => $roleId,
            'value' => $value
        ));

        try{
            $departmentPermissions->save();
        } catch(Exception $e) {
            Mage::logException($e);
        }
    }

    public function adminRolesDeleteAfter($observer)
    {
        $object = $observer->getDataObject();
        $roleId = $object->getId();
        $departmentPermissions = Mage::getModel('helpdeskultimate/department_permissions')->loadByRoleId($roleId);
        if (is_null($roleId)) {
            return;
        }
        try{
            $departmentPermissions->delete();
        } catch(Exception $e) {
            Mage::logException($e);
        }
    }
}
