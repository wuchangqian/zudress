<?php
/* ProductMatrix
 *
 * @category   Webshopapps
 * @package    Webshopapps_productmatrix
 * @copyright  Copyright (c) 2011 WebShopApps Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */


class Webshopapps_Premiumrate_Model_Carrier_Source_Methods {

    public function toOptionArray()
    {
        $premium = Mage::getSingleton('premiumrate/carrier_premiumrate');
    	$arr = array();;
        foreach ($premium->getSimpleAllowedMethods(false) as $k=>$v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }
}
