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

class AW_Helpdeskultimate_Model_Antibot extends AW_Core_Object
{

    /**
     * Checks if seed is valid
     * @param object $seed
     * @return
     */
    public function checkSeed($seed)
    {
        if ($seed) {
            if (!$this->getSeed()) {
                // Seed is empty.
                $this->generateUniqueSeed();
                return true;
            } else {
                // Seed is present. Return seed.
                return $seed == $this->getSeed();
            }
        }
        return false;
    }

    /**
     * Checks if fail key entered correctly
     * @param string $key
     * @param string $enc
     * @return
     */
    public function checkFailKey($key, $enc)
    {
        return md5($key) == $enc;
    }


    /**
     * Generates unique seed and saves it to session
     * @return string
     */
    public function generateUniqueSeed()
    {
        $seed = md5(rand(0, 9999));
        Mage::getSingleton('core/session')->setHDUSeed($seed);
        return $seed;
    }

    public function getSeed()
    {
        return Mage::getSingleton('core/session')->getHDUSeed();
    }
}
