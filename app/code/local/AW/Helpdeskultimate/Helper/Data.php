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

class AW_Helpdeskultimate_Helper_Data extends AW_Helpdeskultimate_Helper_Abstract
{
    const FORMDATAKEY = 'awhdu_formdata';

    const XML_PATH_EMAIL_RECIPIENT = 'helpdeskultimate/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'helpdeskultimate/email/sender_email';

    /*
    * Recursively searches and replaces all occurrences of search in subject values
    * replaced with the given replace value
    * @param string $search The value being searched for
    * @param string $replace The replacement value
    * @param array $subject Subject for being searched and replaced on
    * @return array Array with processed values
    */
    public function recursiveReplace($search, $replace, $subject)
    {
        if (!is_array($subject))
            return $subject;

        foreach ($subject as $key => $value)
            if (is_string($value))
                $subject[$key] = str_replace($search, $replace, $value);
            elseif (is_array($value))
                $subject[$key] = self::recursiveReplace($search, $replace, $value);

        return $subject;
    }

    public function getFiles($folder)
    {
        $out = array();
        if ($dir = @opendir($folder)) {
            while ($file = readdir($dir)) {
                if ($file != '.' && $file != '..') {
                    $out[] = $file;
                }
            }
        }
        return $out;
    }

    public function canShowFrontendForms()
    {
        $departments = Mage::getModel('helpdeskultimate/department')->getCollection()->addActiveFilter();
        return !!count($departments);
    }


    /**
     * Compare param $version with magento version
     * @param string $version Version to compare
     * @return boolean
     */
    public function checkVersion($version)
    {
        return version_compare(Mage::getVersion(), $version, '>=');
    }

    /**
     * Sends file to output stream
     * @param string $path
     * @return
     */
    public function sendFile($path, $basename = null)
    {
        if (file_exists($path)) {
            header("HTTP/1.1 200 OK");
            header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
            header("Cache-Control: None");
            header("Pragma: no-cache");
            header("Accept-Ranges: bytes");
            if (is_null($basename)) {
                $basename = basename($path);
            }
            header("Content-Disposition: inline; filename=\"" . $basename . "\"");
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($path));
            header("Age: 0");
            header("Proxy-Connection: close");
            readfile($path);
            exit();
        } else {
        }
    }

    public function getAllowedExtensions()
    {
        return array('*');
    }


    public function getDepartment($id)
    {
        // Returns department by id
        // If not found - returns default "General department"

        $model = Mage::getModel('helpdeskultimate/department');

        if ($id) {
            $model->load($id);
        }
        return $model;
    }

    /**
     * Converts magento identity to email address
     * @param string $email
     * @return string
     */
    public function getIdentityEmail($email)
    {
        return Mage::getStoreConfig('trans_email/ident_' . $email . '/email');
    }

    /**
     * Creates new seed for customer and puts it to session
     * @return string
     */
    public function getNewSeed()
    {
        if (!($seed = Mage::getSingleton('customer/session')->getHDUSeed())) {
            $seed = md5(rand(0, 999999));
            Mage::getSingleton('customer/session')->setHDUSeed($seed);
        }
        return $seed;
    }

    /**
     * Checks if seed is valid
     * @param string $seed
     * @return bool
     */
    public function isValidSeed($seed)
    {
        return $seed == Mage::getSingleton('customer/session')->getHDUSeed();
    }

    public function getDepEmails()
    {
        return Mage::getModel('helpdeskultimate/department')->getDepEmails();
    }

    public function getGatewayEmails()
    {
        return Mage::getModel('helpdeskultimate/gateway')->getGatewayEmails();
    }

    public function getUploadMaxFileSize()
    {
        return Mage::getStoreConfig('helpdeskultimate/advanced/maxupload');
    }

    public function getCarbonCopy()
    {
        return Mage::getStoreConfig('helpdeskultimate/advanced/carbon_copy');
    }

    public function setRejectedFormData($data)
    {
        if (!($data instanceof Varien_Object))
            $data = new Varien_Object($data);
        $_formData = Mage::getSingleton('adminhtml/session')->getData(self::FORMDATAKEY);
        if (!is_array($_formData)) $_formData = array();
        $_formData[$data->getId() ? $data->getId() : -1] = $data;
        Mage::getSingleton('adminhtml/session')->setData(self::FORMDATAKEY, $_formData);
    }

    public function getRejectedFormData($id = null)
    {
        if (!$id) $id = -1;
        $_formData = Mage::getSingleton('adminhtml/session')->getData(self::FORMDATAKEY);
        return $_formData && isset($_formData[$id]) ? $_formData[$id] : null;
    }

    public function escapeFilename($fileName)
    {
        return preg_replace('/([#=\/*:\?<>\| \\\"\'])+/i', '', $fileName);
    }

    /**
     * Returns md5 hashed filename saving the file extension
     * @param string $filename
     * @return string
     */
    public function getEncodedFileName($filename)
    {
        $filename = $this->escapeFilename($filename);
        $fileNameInfo = pathinfo($filename);
        $_fileName = isset($fileNameInfo['filename']) && $fileNameInfo['filename'] ? $fileNameInfo['filename']
            : substr($filename, 0, strrpos($filename, '.'));
        $_fileExtension = isset($fileNameInfo['extension']) ? $fileNameInfo['extension'] : '';
        $_fullName = md5($_fileName) . '.' . $_fileExtension;
        return $_fullName;
    }

    public function getRealFileName($folder, $file)
    {
        if ($file) {
            if (file_exists($folder . $file)) {
                return $file;
            } elseif (file_exists($folder . $this->getEncodedFileName($file))) {
                return $this->getEncodedFileName($file);
            }
        }
        return false;
    }

    public function isOrderBelongToCustomer($orderId, $customerId, $customerEmail = null)
    {
        $order = Mage::getModel('sales/order')->load(intval($orderId));
        if (is_null($order->getId())) {
            return true;
        }
        $result = (bool)($customerId == $order->getCustomerId());
        if ((!$result && !is_null($customerEmail)) || (is_null($customerId) && is_null($order->getCustomerId()))) {
            $result = (bool)($customerEmail == $order->getCustomerEmail());
        }
        return $result;
    }

    public function isCanSubmitTicket()
    {
        $session = Mage::getSingleton('admin/session');
        if (is_object($session) && is_object($session->getUser()) && $session->getUser()->getId()) {
            return $session->isAllowed('helpdeskultimate/index');
        }
        return false;
    }
}
