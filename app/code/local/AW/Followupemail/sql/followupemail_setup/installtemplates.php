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
 * @package    AW_Followupemail
 * @version    3.5.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


// template loader

libxml_use_internal_errors(true);
try
{
    $filename = Mage::getModel('core/config')->getOptions()->getCodeDir() . DS . 'local' . DS . 'AW' . DS . 'Followupemail' . DS . 'sql' . DS . 'followupemail_setup' . DS . 'templates.xml';

    $xml = simplexml_load_file($filename, 'SimpleXMLElement', LIBXML_NOCDATA);

    if (!$xml) {
        foreach (libxml_get_errors() as $error) {
            $message = 'Failed to load XML : ' . $error->message;
            $subject = "Failed to load XML";
            Mage::getSingleton('followupemail/log')->logError($message, $this, $subject);
        }

        return;
    }

    libxml_clear_errors();

    define('TEMPLATE_PREFIX', 'template="nsltr:');

    $templates = array();
    $existingTemplates = array();
    $model = Mage::getModel('newsletter/template');
    foreach ($xml as $template) {
        $data = array();
        foreach ($template as $fieldName => $value) {
            $data[$fieldName] = (string) $template->$fieldName;
        }

        if (!isset($data['template_code'])) continue;
        if ($model->loadByCode($data['template_code'])->getId()) {
            $code = $this->_moduleConfig->version;
            $existingTemplates[$data['template_code']] = $code;
            $data['template_code'] = $data['template_code'] . '_' . $code;
        }

        $templates[] = $data;
    }

    foreach ($templates as $data) {
        foreach ($existingTemplates as $k => $v) {
            $data['template_text'] = str_replace(TEMPLATE_PREFIX . $k . '"', TEMPLATE_PREFIX . $k . '_' . $v . '"', $data['template_text']);
        }

        $model
            ->setData($data)
            ->setTemplateId(null)
            ->setTemplateType(Mage_Newsletter_Model_Template::TYPE_TEXT)
            ->setTemplateActual(1)
            ->save();
        AW_Followupemail_Model_Log::log(print_r($model->getData(), true));
    }
} catch (Exception $e) {
    Mage::logException($e);
}
