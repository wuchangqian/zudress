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


class AW_Helpdeskultimate_Block_Adminhtml_Form_Element_Testconnection extends Mage_Adminhtml_Block_Template
{
    /**
     * Path to template
     */
    const TEMPLATE_PATH = 'helpdeskultimate/form/element/testconnection.phtml';

    /**
     * Basical states
     * @var array
     */

    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);
        $this->_states = array(
            array(
                'value' => AW_Helpdeskultimate_Model_Form_Element_Testconnection::STATUS_SUCCESS,
                'label' => $this->__('Succeed'),
                'color' => 'green'
            ),
            array(
                'value' => AW_Helpdeskultimate_Model_Form_Element_Testconnection::STATUS_FAIL,
                'label' => $this->__('Failed'),
                'color' => 'red'
            ),
        );
    }

    /**
     * Retrives rendered status
     * @param integer $res Result
     * @return string
     */
    public function getStateHtml()
    {
        if (!$this->getConnectionState()) {
            return '';
        }
        foreach ($this->_states as $state) {
            if ($state['value'] == $this->getConnectionState()) {
                return $this->renderStateHtml($state['label'], $state['color']);
            }
        }
    }

    /**
     * Retrives translated and colored label
     * @param string $label Label
     * @param string $color Color of label
     * @return string
     */
    public function renderStateHtml($label, $color)
    {
        $label = $this->__($label);
        return "<strong style=\"color: {$color};\">{$label}</strong>";
    }

    /**
     * Retrives array of States
     * @return array
     */
    public function getStateObjects()
    {
        $states = array();
        foreach ($this->_states as $state) {
            $states[] = new Varien_Object($state);
        }
        return $states;
    }
}