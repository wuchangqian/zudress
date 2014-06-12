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


class AW_Helpdeskultimate_Model_Source_Users extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrive all attribute options
     *
     * @return array
     */

    static $userName = "";

    protected $_collection;

    public function __construct()
    {
        //parent::__construct();echo 2;
        $this->_collection = Mage::getModel('customer/customer')->getCollection()
                ->addNameToSelect()
                ->addAttributeToSelect('email')
                ->addAttributeToSelect('firstname')
                ->addAttributeToSelect('lastname');
    }

    public function getAllOptions()
    {
        // Return users

        $_options = array(
            // array('value' => 0, 'label' => '--- Please select ---'),
        );

        if (self::$userName) {
            $this->addNameFilter(self::$userName);
        }


        $collection = $this->_collection
                ->setOrder('email', 'asc')
                ->load();

        //print_r($this->_collection->getSelect()->assemble());

        if ($collection) {
            foreach ($collection as $customer) {
                array_push(
                    $_options,
                    array(
                        'value' => $customer->getId(),
                        'label' => $customer->getName() . " &lt;{$customer->getEmail()}&gt;"
                    )
                );
            }
        }

        return $_options;
    }

    public function addNameFilter($letter)
    {
        $this->_collection->addAttributeToFilter(
            array(
                 array('attribute' => 'firstname', 'like' => '%' . $letter . '%'),
                 array('attribute' => 'lastname', 'like' => '%' . $letter . '%'),
                 array('attribute' => 'email', 'like' => '%' . $letter . '%')
            )
        );
        return $this;
    }

    public function addEmailFilter($letter)
    {
        $this->_collection->addAttributeToFilter(
            array(
                 array('attribute' => 'email', 'like' => '%' . $letter . '%')
            )
        );
        return $this;
    }


    public function getCollection()
    {
        return $this->_collection;
    }
}
