<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Usa
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract USA shipping carrier model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
/**
 * Magento Webshopapps Module
 *
 * @category   Webshopapps
 * @package    Webshopapps Wsacommon
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    www.webshopapps.com/license/license.txt
 * @author     Karen Baker <sales@webshopapps.com>
 *
 *********** DEPRECATED - Now moved into WSAFreight Common - do not modify, here to support extn installs only ************************
 *
*/

abstract class Webshopapps_Wsacommon_Model_Shipping_Carrier_Abstract extends Webshopapps_Wsacommon_Model_Shipping_Carrier_Baseabstract
{

    const USA_COUNTRY_ID = 'US';
    const PUERTORICO_COUNTRY_ID = 'PR';
    const GUAM_COUNTRY_ID = 'GU';
    const GUAM_REGION_CODE = 'GU';

    public function isCityRequired()
    {
        return false;
    }

 	/**
     * Determine whether zip-code is required for the country of destination
     *
     * @param string|null $countryId
     * @return bool
     */
 	public function isZipCodeRequired($countryId = null)
    {
        if (!is_null($countryId)) {
	  return !Mage::helper('directory')->isZipCodeOptional($countryId);
        }
        return true;
    }

    /**
     * Processing additional validation to check is carrier applicable.
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Carrier_Abstract|Mage_Shipping_Model_Rate_Result_Error|boolean
     */
    public function proccessAdditionalValidation(Mage_Shipping_Model_Rate_Request $request)
    {
        return true;
    }
    
    
     protected function getLineItems($ignoreFreeItems, $useParent=true) {
       	$LineItemArray=array();
       	if(!Mage::helper('wsacommon')->isModuleEnabled('Webshopapps_Wsafreightcommon')){
       		$defaultFreightClass = $this->getConfigData('default_freight_class');
       	} else $defaultFreightClass = Mage::helper('wsafreightcommon')->getDefaultFreightClass();   		
   		
       	 foreach ($this->_request->getAllItems() as $item) {
       	 	      	 	
       	 	$weight=0;
   			$qty=0;
   			$price=0;
   			
   			if (!Mage::helper('wsacommon/shipping')->getItemTotals($item, $weight,$qty,$price,$useParent,$ignoreFreeItems)) {
   				continue;
   			}
   			
       	 	$product = Mage::helper('wsacommon/shipping')->getProduct($item,$useParent);
   			
   			$weight=ceil($weight);  // round up to nearest whole - required for conway
   			$price=$price/$qty;
       		$class=$product->getData('freight_class');
   			
       		if (empty($class) || $class=='') {
       			$class=$defaultFreightClass; // use default
       		}
   			
   			if (empty($LineItemArray) || !array_key_exists($class,$LineItemArray)) {
   				$LineItemArray[$class]= $weight;
   			} else {
   				
   				$LineItemArray[$class]= $LineItemArray[$class]+ ($weight);
   			}
   			
       	 }
       	 return $LineItemArray;
	}
    
  
    protected function getShipmentMethods($allowedMethods) {
    	$shipmentMethods=array();
    	foreach ($allowedMethods as $method) {
    		$shipmentMethods[]=$this->getCode('method',$method);
    	}
    	return $shipmentMethods;
    }
    
    
    public function getAllowedMethods()
    {
        $arr = array();
    	$allowed = explode(',', $this->getConfigData('allowed_methods'));
     	foreach ($allowed as $k) {
             $arr[$k] =  $this->getCode('method', $k);
         }
         
    	return $arr;
    }  
    
	protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }
    
 	protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }
}
