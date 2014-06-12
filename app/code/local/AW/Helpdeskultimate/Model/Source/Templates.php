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


class AW_Helpdeskultimate_Model_Source_Templates extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const NO_TEMPLATE = 0;

    const NO_TEMPLATE_LABEL = '--- No template ---';

    /**
     * Retrive all attribute options
     *
     * @return array
     */

    public function getAllOptions()
    {
        // Return users

        $_options = array(
            array(
                  'value' => self::NO_TEMPLATE,
                  'label' => Mage::helper('helpdeskultimate')->__(self::NO_TEMPLATE_LABEL)
            ),
        );
        try {
            $collection = Mage::getModel('helpdeskultimate/template')
                    ->getCollection()
                    ->setActiveFilter()
                    ->load();
            if ($collection) {
                foreach ($collection as $tpl) {
                    array_push($_options, array('value' => $tpl->getId(), 'label' => $tpl->getName()));
                }
            }
        } catch (Exception $e) {
        }
        return $_options;
    }

    public function getFlatOptions()
    {

        $_options = array(
            self::NO_TEMPLATE => self::NO_TEMPLATE_LABE
        );

        try {
            $collection = Mage::getModel('helpdeskultimate/template')->getCollection()
                    ->setActiveFilter()
                    ->load();
            if ($collection) {
                foreach ($collection as $tpl) {
                    $_options[$tpl->getId()] = $tpl->getName();
                }
            }
        } catch (Exception $e) {
        }
        return $_options;
    }

}
