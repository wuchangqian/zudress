<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';
class MW_Onestepcheckout_IndexController extends Mage_Checkout_OnepageController
//class MW_Onestepcheckout_IndexController extends Mage_Core_Controller_Front_Action
{	protected $notshiptype=0;
	public function getCheckout(){
		return Mage::getSingleton('checkout/session');
	}
	public function getQuote(){
		return Mage::getSingleton('checkout/session')->getQuote();
	}
    public function indexAction()
    {
		 // if($_pAddsses = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling())
		 // $aa = Mage::getSingleton('customer/session')->getCustomer()->getAddressById($_pAddsses)->getData('country_id');
			// var_dump($aa);die();
		// Mage::getSingleton('checkout/session')->unsetAll();
		// exit;				//	xoa' session
		//$collect=Mage::getModel()

////////////////////geoip///////		
		$this->getGeoip();		
		
////////////////////////////
        if(!Mage::getStoreConfig('onestepcheckout/config/allowguestcheckout')){
    		if(!Mage::getSingleton('customer/session')->isLoggedIn()){
				$this->_redirect('customer/account/login');
				return;
    		}
		}	
		if($this->_initAction()){     
			if(Mage::getStoreConfig('onestepcheckout/config/enabled')){
				//performance remove block 
				if($blocks=$this->getLayout()->getBlock('checkout.onepage')){	//neu ton tai block checkout.onepage
				$blocks=$this->getLayout()->getBlock('checkout.onepage')->unsetChildren();	//remove block checkout.onepage cua magento
				}
				//var_dump($blocks);die();
			}
			else{	// remove block onestepcheckout.daskboard va` thiet lap lai block checkout cua magento
			
			$blocks=$this->getLayout()->getBlock('content')->unsetChild('onestepcheckout.daskboard');
			$skin=$this->getLayout()->getBlock('head')->unsetChild('onestepcheckout.head');
			$template=$this->getLayout()->getBlock('root')->setTemplate('page/2columns-right.phtml');
			}
			$this->renderLayout();
		}
		else
			$this->_redirect('checkout/cart');

    }
	
    public function _initAction()
    {
        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            //$this->_redirect('checkout/cart');
            return false;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            //$this->_redirect('checkout/cart');
            return false;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            //$this->_redirect('checkout/cart');
            return false;
        }
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));
        $this->getOnepage()->initCheckout();
		
		//luu thong tin bat ki` de? show duoc method shipping
		// Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()	// ->setCountryId('US')            // ->setCity('dsad')			// ->setPostcode('12345')			 // ->setLastname('sfsf ')			 // ->setFirstname('fsdf')			 // ->setStreet('fsdf')             // ->setRegionId('4')             // ->setRegion('asdsad')			 // ->setTelephone(' 23424234');           // ->setCollectShippingRates(true);		// Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()			// ->setCountryId('US')            // ->setCity('dsad')             // ->setPostcode('12345')			 // ->setSameAsBilling(1)			 // ->setLastname('sfsf ')			 // ->setFirstname('fsdf')			 // ->setStreet('fsdf')             // ->setRegionId('4')             // ->setRegion('asdsad')			 // ->setTelephone(' 23424234')            // ->setCollectShippingRates(true);		// $this->_getQuote()->save();		//var_dump(Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethod());die;

		$this->initInfoaddress();//khoi tao address taam	
		$defaultpaymentmethod=$this->initpaymentmethod();  // ex: $defaultpaymentmethod='checkmo';
		if($defaultpaymentmethod){
			//Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->collectShippingRates();
			try{
			Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setPaymentMethod($defaultpaymentmethod);
			//Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
			$payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();
			$payment->importData(Array('method'=>$defaultpaymentmethod));
			}catch(Exception $e){
				//return;
			}
		}
		$defaultshippingmethod=$this->initshippingmethod();  //ex: $defaultshippingmethod='flatrate_flatrate';
		if($defaultshippingmethod){
			//Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->collectShippingRates();
			Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setShippingMethod($defaultshippingmethod);
			//Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
		}
		$applyrule=$this->getQuote()->getAppliedRuleIds();
		$applyaction=Mage::getModel('salesrule/rule')->load($applyrule)->getSimpleAction();
		//echo"<pre>";var_dump($applyaction);die;
		if($applyaction!='cart_fixed'){
			Mage::getSingleton('checkout/session')->getQuote()->setTotalsCollectedFlag(false);
		}	
		///////////////end init address/////////////////
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
		$this->_initLayoutMessages('checkout/session');
		$this->_initLayoutMessages('catalog/session');
		Mage::getSingleton('catalog/session')->getData('messages')->clear();
		Mage::getSingleton('checkout/session')->getData('messages')->clear();	// clear het message thong bao vi trong template coupon co nhung' code xuat message thong bao'
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
		return true;
    }
	public function initshippingmethod(){
		//$currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
		//$addresses = $currentCustomer->getAddresses();
		$listmethod='';
		$guessCustomer = Mage::getSingleton('checkout/session')->getQuote();
		$addresses=$guessCustomer->getShippingAddress();  //ship method ko luu trong billing ma luu trong shipping
		//$addresses=$guessCustomer->getBillingAddress();
		//$methodship=$addresses->getShippingMethod();//return shipping method da dc selected
		$list_shipmethod=$addresses->getGroupedAllShippingRates();
		
		foreach ($list_shipmethod as $code => $_rates){
			$listmethod[]=$code;
		}
		//var_dump($listmethod);die;
		if(!$guessCustomer->isVirtual()){
			if($listmethod==null){return;}
			if(sizeof($listmethod)==1){
			//	$this->getOnepage()->saveShippingMethod($listmethod[0]);	
				return $listmethod[0].'_'.$listmethod[0];
			}else{
				foreach($listmethod as $methodname){//echo $methodname.'=='.Mage::getStoreConfig("onestepcheckout/config/default_shippingmethod")."<br>";
					if(Mage::getStoreConfig("onestepcheckout/config/default_shippingmethod")==$methodname.'_'.$methodname){
								return $methodname.'_'.$methodname;
						//$this->getOnepage()->saveShippingMethod($methodname.'_'.$methodname);	// vi` tham so truyen vao can o dang freeshipping_freeshipping, ma $methodname chi? la freeshipping
						//Mage::getSingleton('checkout/session')->getQuote()->setTotalsCollectedFlag(false);
						//Mage::getSingleton('checkout/session')->getQuote()->collectTotals()->save();
					}
				}
			}
		}
		return;
	}
    public function _canUseMethod($method) //ham kiem tra payment method co thoa man cac dieu kien ko
    {
        if (!$method->canUseForCountry($this->getQuote()->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency(Mage::app()->getStore()->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = Mage::getSingleton('checkout/session')->getQuote()->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }
        return true;
    }
	public function initpaymentmethod(){
		$listmethod='';
		
		//$currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
		//$addresses = $currentCustomer->getAddresses();
		
		$guessCustomer = Mage::getSingleton('checkout/session')->getQuote();
		//var_dump($guessCustomer->getBillingAddress()->getPayment());die;
		$store = $guessCustomer ? $guessCustomer->getStoreId() : null;
		$methods = Mage::helper('payment')->getStoreMethods($store, $guessCustomer);
		//$billingCountry=''
		
      //  if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
        $billingCountry = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getCountryId();
		//}
		foreach ($methods as $key => $method) {// echo get_class($methods);die;
			if ($this->_canUseMethod($method)) {
			//	$this->_assignMethod($method);
				//if($method->canUseForCountry($billingCountry)){
					$listmethod[]=$method->getCode();
				//}			
			}
			// else {
				// unset($methods[$key]);
			// }
			//echo get_class($method);
		}//die;
		//echo"<pre>";var_dump($listmethod);die;
		try{
			if($listmethod ==null or $listmethod==''){return;}
			if(sizeof($listmethod)==1){
				//$this->getOnepage()->savePayment($listmethod[0]);
				return $listmethod[0];
			}else{
				foreach($listmethod as $methodname){
					if(Mage::getStoreConfig("onestepcheckout/config/default_paymentmethod")==$methodname){//echo $methodname.'=='.Mage::getStoreConfig("onestepcheckout/config/default_paymentmethod")."<br>";
						//$this->getOnepage()->savePayment(Array('method'=>$methodname));	
						// Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setPaymentMethod($methodname);
						// Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setCollectShippingRates(true);
						// $payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();
						// $payment->importData(Array('method'=>$methodname));
						// //echo $methodname;die;
						return $methodname;
					}
				}
			}
			return;
        }catch (Exception $e) {
			echo $e->getMessage();die;
		}			
	}
	
	public function initInfoaddress(){
			$coutryid='';$postcode='';$region='';$regionid='';$city='';$customerAddressId='';

				if(Mage::getStoreConfig('onestepcheckout/config/enable_geoip')){
					$coutryid=Mage::registry('Countrycode');
					$postcode=Mage::registry('Zipcode');
					$region=Mage::registry('Regionname');
					if(Mage::getModel('customer/address_abstract')->getRegionModel(Mage::registry('Regioncode')))	//kiem tra regioncode co ton tai hay ko
						$regionid=Mage::registry('Regioncode');
					$city=Mage::registry('City');
				}
				elseif (Mage::getStoreConfig('onestepcheckout/config/default_country')) {
					 $coutryid = Mage::getStoreConfig('onestepcheckout/config/default_country');	// xuat ra voi' default country duoc cau hinh trong onestepcheckout
				}
				else {
					$coutryid = Mage::getStoreConfig('general/country/default');	// xuat ra voi' default country duoc cau hinh trong GENERAL DEFAULT CUA MAGENTO
				}
		
				$postData=array(
					'address_id'=>'',
					'firstname'=>'',
					'lastname'=>'',
					'company'=>'',
					'email'=>'',
					'street'=>array('',''),
					'city'=>$city,
					'region_id'=>$regionid,
					'region'=>$region,	
					'postcode'=>$postcode,
					'country_id'=>$coutryid,
					'telephone'=>'',
					'fax'=>'',
					'save_in_address_book'=>'0'
				);
			//echo"<pre>";var_dump( $postData);die;
        	//$postData=$this->getRequest()->getPost($isbilling, array());
			if(Mage::getSingleton('customer/session')->isLoggedIn()){
				$customerAddressId =Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();    
				//echo $customerAddressId;die;
			}				
        	//if($this->getRequest()->getPost('ship_to_same_address')=="1"){
        		//$isbilling="billing";
        		//$postData=$this->getRequest()->getPost($isbilling, array());
        	if(($postData['country_id']!='')  OR $customerAddressId){
	        		$postData = $this->filterdata($postData);
	        		
					//echo"<pre>";var_dump($postData);die();
					if(version_compare(Mage::getVersion(),'1.4.0.1','>='))
						$data = $this->_filterPostData($postData);
					else
						$data=$postData;
	           	 	
	        	   	if(isset($data['email'])) {
	                	$data['email'] = trim($data['email']);
	            	}
	
				$this->saveBilling($data, $customerAddressId);
	            //	$result = $this->getOnepage()->saveBilling($data, $customerAddressId);
				$this->saveShipping($data, $customerAddressId);
				//	$result = $this->getOnepage()->saveShipping($data, $customerAddressId);
	            //	}else
	            //	$result = $this->getOnepage()->saveShipping($data, $customerAddressId);
        		}
        		else{
 					 $this->_getQuote()->getShippingAddress()
					 ->setCountryId('')
					 ->setPostcode('')	
					 ->setCollectShippingRates(true);
					 $this->_getQuote()->save();
					 $this->loadLayout()->renderLayout();  
					 return;        			
        		}	
	}
    public function saveShipping($data, $customerAddressId)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => $this->_helper->__('Invalid data.'));
        }
        $address = $this->getQuote()->getShippingAddress();

        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => $this->_helper->__('Customer Address is not valid.')
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
            unset($data['address_id']);
            $address->addData($data);
        }
        $address->implodeStreetAddress();
        $address->setCollectShippingRates(true);

        // if (($validateRes = $address->validate())!==true) {
            // return array('error' => 1, 'message' => $validateRes);
        // }

        //$this->getQuote()->collectTotals()->save();

        // $this->getCheckout()
            // ->setStepData('shipping', 'complete', true)
            // ->setStepData('shipping_method', 'allow', true);
    }
    public function saveBilling($data, $customerAddressId)
    {
		if (empty($data)) {
					return array('error' => -1, 'message' => $this->_helper->__('Invalid data.'));
		}
		// //echo 'why:'.$data['save_in_address_book'];die();
        $address = $this->getQuote()->getBillingAddress();
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => $this->_helper->__('Customer Address is not valid.')
                    );
                }
                $address->importCustomerAddress($customerAddress);
            }
        } else {
             unset($data['address_id']);
             $address->addData($data);
            //$address->setId(null);
        }

        // if (($validateRes = $address->validate())!==true) {
            // return array('error' => 1, 'message' => $validateRes);
        // }
		
        // if (!$this->getQuote()->getCustomerId() && self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            // if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                // return array('error' => 1, 'message' => $this->_customerEmailExistsMessage);
            // }
        // }

         $address->implodeStreetAddress();

        if (!$this->getQuote()->isVirtual()) {
            // /**
             // * Billing address using otions
             // */
            // $usingCase = isset($data['use_for_shipping']) ? (int) $data['use_for_shipping'] : 0;

            // switch($usingCase) {
                // case 0:
                     $shipping = $this->getQuote()->getShippingAddress();
                     $shipping->setSameAsBilling(0);
                    // break;
                // case 1:
                    // $billing = clone $address;
                    // $billing->unsAddressId()->unsAddressType();
                    // $shipping = $this->getQuote()->getShippingAddress();
                    // $shippingMethod = $shipping->getShippingMethod();
                    // $shipping->addData($billing->getData())
                        // ->setSameAsBilling(1)
                        // ->setShippingMethod($shippingMethod)
                        // ->setCollectShippingRates(true);
                    // $this->getCheckout()->setStepData('shipping', 'complete', true);
                    // break;
            // }
        }

        // if (true !== $result = $this->_processValidateCustomer($address)) {
            // return $result;
        // }

      //  $this->getQuote()->collectTotals();
      //  $this->getQuote()->save();

        // $this->getCheckout()
            // ->setStepData('billing', 'allow', true)
            // ->setStepData('billing', 'complete', true)
            // ->setStepData('shipping', 'allow', true);		
    }
    protected function _processValidateCustomer(Mage_Sales_Model_Quote_Address $address)
    {
        // set customer date of birth for further usage
        $dob = '';
        if ($address->getDob()) {
            $dob = Mage::app()->getLocale()->date($address->getDob(), null, null, false)->toString('yyyy-MM-dd');
            $this->getQuote()->setCustomerDob($dob);
        }

        // set customer tax/vat number for further usage
        if ($address->getTaxvat()) {
            $this->getQuote()->setCustomerTaxvat($address->getTaxvat());
        }

        // set customer gender for further usage
        if ($address->getGender()) {
            $this->getQuote()->setCustomerGender($address->getGender());
        }

        // invoke customer model, if it is registering
        if ($this->getQuote()->getCheckoutMethod()=='register') {
            // set customer password hash for further usage
            $customer = Mage::getModel('customer/customer');
            $this->getQuote()->setPasswordHash($customer->encryptPassword($address->getCustomerPassword()));

            // validate customer
            foreach (array(
                'firstname'    => 'firstname',
                'lastname'     => 'lastname',
                'email'        => 'email',
                'password'     => 'customer_password',
                'confirmation' => 'confirm_password',
                'taxvat'       => 'taxvat',
                'gender'       => 'gender',
            ) as $key => $dataKey) {
                $customer->setData($key, $address->getData($dataKey));
            }
            if ($dob) {
                $customer->setDob($dob);
            }
            $validationResult = $customer->validate();
            if (true !== $validationResult && is_array($validationResult)) {
                return array(
                    'error'   => -1,
                    'message' => implode(', ', $validationResult)
                );
            }
        } elseif(self::METHOD_GUEST == $this->getQuote()->getCheckoutMethod()) {
            $email = $address->getData('email');
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                return array(
                    'error'   => -1,
                    'message' => $this->_helper->__('Invalid email address "%s"', $email)
                );
            }
        }

        return true;
    }
	public function updateshippingmethodAction()
	{
		$data = $this->getRequest()->getPost('shipping_method');
		$this->getOnepage()->saveShippingMethod($data);
		$this->loadLayout()->renderLayout();
	}
	public function updatepaymentmethodAction()
	{
		$data=$this->getRequest()->getPost('payment');		//#get param from ajax
		//echo $data; die();
		try{
		$this->getOnepage()->savePayment($data);
            }
	        catch (Exception $e) {
             				//echo $e->getMessage();
             				echo "<script type='text/javascript'>alert('".$e->getMessage()."');</script>";//cho` 2 s de khach hang nhin thay loi va redirect
             				return;
			}
		$this->loadLayout()->renderLayout();		
	}
	public function updateemailmsgAction(){
		$email = (string) $this->getRequest()->getParam('email');
		$websiteid=Mage::app()->getWebsite()->getId();
		$store=Mage::app()->getStore();
		$customer=Mage::getSingleton('customer/customer');
		$customer->website_id=$websiteid;
		$customer->setStore($store);
		$customer->loadByEmail($email);
		if($customer->getId()){
			//Mage::getSingleton('checkout/session')->addSuccess( 	$this->__('Your email "%s" is valid.', Mage::helper('core')->htmlEscape($email))	);
			echo "0";
		}
		else {
			echo "1";
			//Mage::getSingleton('checkout/session')->addError(	$this->__('Your email "%s" is not valid.', Mage::helper('core')->htmlEscape($email))	);
		}
		return;
		// $layout=$this->getLayout();
		// $this->_initLayoutMessages('checkout/session');	// khoi tao message cho phep hien thi message ra man hinh
		// $layout->createBlock('core/messages', 'messages');//->getGroupedHtml();die();
		
		// $update = $layout->getUpdate();
		// $update->load('checkout_onepage_review');
		// $layout->generateXml();
		// $layout->generateBlocks();
		// $output = $layout->getBlock('messages')->toHtml();		//lay rieng block checkout.cart.coupon trong layout checkout_cart_index
		// return $output;
		//echo $this->getMessagesBlock()->getGroupedHtml();die();
	}
	public function removeproductAction(){
			$id = (int) $this->getRequest()->getParam('id');
			$hasgiftbox=$this->getRequest()->getParam('hasgiftbox');
			if ($id) {
				try {
					Mage::getSingleton('checkout/cart')	->removeItem($id)
														->save();
														
				} catch (Exception $e) {
					//$this->_getSession()->addError($this->__('Cannot remove the item.'));
					$success=0;
					echo '{"r":"'.$success.'","error":"'.Mage::helper('onestepcheckout')->__('Cannot remove the item.').'","view":' .json_encode($this->renderReview()).'}';die;
					return ;					
				}
			}
		//$this->_redirectReferer(Mage::getUrl('*/*'));	
		$success=1; //gan' bien' success la co' error hay ko error , lay error tu message
		//$this->renderReview();
		if (!$this->_getQuote()->getItemsCount()) {	
			echo '{"r":"'.$success.'","view":"<script type=\"text/javascript\">window.location=\"'.Mage::getUrl('checkout/onepage').'\"</script>"}';die;
			return;//return giup thoat ra va ko thuc hien cac lenh con lai, neu thuc hien tiep se gay loi, vi ko con item nao trong gio hang			
		}
		else{
			if($hasgiftbox){ //neu co gift box thi phai tra ve du lieu update cho gift box
				echo '{"r":"'.$success.'","view":' .json_encode($this->renderReview()).',"giftbox":' .json_encode($this->renderGiftbox()) .'}';die;
				return ;
			}
			else{
				echo '{"r":"'.$success.'","view":' .json_encode($this->renderReview()).'}';die;
				return ;				
			}
		}
	}
	public function updateqtyAction(){
        try {
            $cartData = $this->getRequest()->getParam('cart');
			//echo"<pre>";var_dump($cartData);die;
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter($data['qty']);
                    }
                }
                $cart = Mage::getSingleton('checkout/cart');
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }
                $cart->updateItems($cartData)
                    ->save();
            }
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        }
        catch (Mage_Core_Exception $e) {
           // $this->_getSession()->addError($e->getMessage());
			$success=0;
			echo '{"r":"'.$success.'","error":"'.$e->getMessage().'","view":' .json_encode($this->renderReview()).'}';die;
			return ;			
        }
        catch (Exception $e) {
           // $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
			$success=0;
			echo '{"r":"'.$success.'","error":"'.Mage::helper('onestepcheckout')->__('Cannot update shopping cart.').'","view":' .json_encode($this->renderReview()).'}';die;
			return ;			
        }	
		$success=1; //gan' bien' success la co' error hay ko error , lay error tu message
		//$this->renderReview();
		if (!$this->_getQuote()->getItemsCount()) {	
			echo '{"r":"'.$success.'","view":"<script type=\"text/javascript\">window.location=\"'.Mage::getUrl('checkout/onepage').'\"</script>"}';die;
			return;//return giup thoat ra va ko thuc hien cac lenh con lai, neu thuc hien tiep se gay loi			
		}
		else{
			echo '{"r":"'.$success.'","view":' .json_encode($this->renderReview()).'}';die;
			return ;			
		}		
	}
	public function updatecouponAction(){
		$this->_initLayoutMessages('checkout/session');
		if (!$this->_getQuote()->getItemsCount()) {
            //$this->_goBack();
			echo '{"r":"0","coupon":'.json_encode($this->renderCoupon()).',"view":' . json_encode($this->renderReview()).'}';die;
            return;
        }
		$couponCode = (string) $this->getRequest()->getParam('coupon_code');
		if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
		$oldCouponCode = $this->_getQuote()->getCouponCode();//get couponcode cu~ da duoc apply truoc' do'
        if (!strlen($couponCode) && !strlen($oldCouponCode)) {		//2 nay deu rong thi` price ko do
         //   $this->_goBack();
			echo '{"r":"0","coupon":'.json_encode($this->renderCoupon()).',"view":' . json_encode($this->renderReview()).'}';die;
            return;
        }
        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
		        ->collectTotals()
                ->save();
			//$this->sendHtmlto();
				if ($couponCode) {
					if ($couponCode == $this->_getQuote()->getCouponCode()) {
						Mage::getSingleton('checkout/session')->addSuccess(
							$this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
						);
					}
					else {
						Mage::getSingleton('checkout/session')->addError(
							$this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
						);
					}
				} else {
					Mage::getSingleton('checkout/session')->addSuccess($this->__('Coupon code was canceled.'));
				}				
		}
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('checkout/session')->addError($e->getMessage());
		}
        catch (Exception $e) {
            Mage::getSingleton('checkout/session')->addError($this->__('Cannot apply the coupon code.'));
        }
		$this->_initLayoutMessages('checkout/session');
		$success=1; //gan' bien' success la co' error hay ko error , lay error tu message
		//$this->renderReview();
		echo '{"r":"'.$success.'","coupon":'.json_encode($this->renderCoupon()).',"view":' .json_encode($this->renderReview()).'}';die;
		return ;
	}
	public function renderReview(){
		$layout=$this->getLayout();
		$update = $layout->getUpdate();
		$update->load('checkout_onepage_index');
		$layout->generateXml();
		$layout->generateBlocks();
		//$layout->getBlock('root')->toHtml(); //lay rieng block
		//$output = $layout->getOutput();	//lay tat ca block trong layout
		//2 lenh tren cho phep lay' block hoac tat ca block, ta chi can 1 lenh trong truong hop nay: la lenh sau
		$output=$layout->getBlock('info')->toHtml();
		return $output;
		//$this->getResponse()->setBody($output);	
			//echo json_encode(array('param1'=>1,'param2'=>2););
	}
	public function renderCoupon(){
		$layout=$this->getLayout();
		$update = $layout->getUpdate();
		$update->load('checkout_onepage_index');
		$layout->generateXml();
		$layout->generateBlocks();
		
		$output = $layout->getBlock('checkout.onepage.coupon')->toHtml();		//lay rieng block checkout.cart.coupon trong layout checkout_cart_index
		return $output;
		//$this->getResponse()->setBody($output);			
	}
	public function renderGiftbox(){
		$layout=$this->getLayout();
		$update = $layout->getUpdate();
		$update->load('checkout_onepage_index');
		$layout->generateXml();
		$layout->generateBlocks();
		
		$output = $layout->getBlock('onestepcheckout.onepage.shipping_method.additional')->toHtml();		//lay rieng block checkout.cart.coupon trong layout checkout_cart_index
		return $output;
		//$this->getResponse()->setBody($output);			
	}
	public function updatepaymenttypeAction(){
		if($this->notshiptype==1){
				$this->loadLayout()->renderLayout();
		}
		else{
			$this->updateshippingtypeAction();
		}
	}
	public function updateshippingtypeAction()
    //public function saveBillingAction()
    {	$this->notshiptype=1;
//$data=$this->getRequest()->getPost($isbilling,array());
        if ($this->getRequest()->isPost()) {
        	$isbilling="billing";
        	if($this->getRequest()->getPost('ship_to_same_address')=="1"){
        		$isbilling="billing";
        	}
        	else{
        		$isbilling="shipping";
        	}
        	$postData=$this->getRequest()->getPost($isbilling, array());
			$customerAddressId = $this->getRequest()->getPost($isbilling.'_address_id', false);    	
        	//if($this->getRequest()->getPost('ship_to_same_address')=="1"){
        		//$isbilling="billing";
        		//$postData=$this->getRequest()->getPost($isbilling, array());
        		if(($postData['country_id']!='')  OR $customerAddressId){
	        		$postData = $this->filterdata($postData);
	        		$postData['use_for_shipping']='1';
					//echo"<pre>";var_dump($postData);die();
					if(version_compare(Mage::getVersion(),'1.4.0.1','>='))
						$data = $this->_filterPostData($postData);
					else
						$data=$postData;
	           	 	
	        	   	if(isset($data['email'])) {
	                	$data['email'] = trim($data['email']);
	            		}
	            	if($isbilling="billing"){//echo"break";die();
	            	$result = $this->getOnepage()->saveBilling($data, $customerAddressId);}
	            	else
	            	$result = $this->getOnepage()->saveShipping($data, $customerAddressId);
        		}
        		else{
 					 $this->_getQuote()->getShippingAddress()
					 ->setCountryId('')
					 ->setPostcode('')	
					 ->setCollectShippingRates(true);
					 $this->_getQuote()->save();
					 $this->loadLayout()->renderLayout();  
					 return;        			
        		}
	    }
			
		$this->loadLayout()->renderLayout();
    }
	
	// public function cancel_updateshippingtypeAction()
	// {	
		
		// //$str=$this->getRequest()->getPost('str_value');
		// //$add=explode(",",$str);
		// $add=explode(",",$this->getRequest()->getPost('str_value'));
	// //	var_dump($add);die();
		// if($add[0]){// AND $add[0]!='undefined'){
				// //$currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
				// //$addresses = $currentCustomer->getAddresses();
				// $addresses = Mage::getSingleton('customer/session')->getCustomer()->getAddresses();
				// //Mage::log($addresses);
				// $data="";
				// foreach($addresses as $add){
					// $current_id=$add->getData('entity_id');
					// //Mage::log($add->getData('entity_id'));
					// if($current_id==$add[0]){
						// $data=$add->getData('country_id');
						// break;
					// }
				// }
				// $this->_getQuote()->getShippingAddress()
				// ->setCountryId($data)
				// ->setCollectShippingRates(true);
				// $this->_getQuote()->save();
				// $this->loadLayout()->renderLayout();
				// return;			
		// }
		// else{
				// //	$data=$add[1];
				// if($add[1]){// AND $add[1]!='undefined'){
					// $this->_getQuote()->getShippingAddress()
					// ->setCountryId($add[1])
					// ->setPostcode($add[2])
					// ->setRegionId($add[3])
					// ->setRegion($add[4])
					// ->setCity($add[5])
					// ->setCollectShippingRates(true);
					// $this->_getQuote()->save();
					// $this->loadLayout()->renderLayout();
				// }
				// else{
					 // $this->_getQuote()->getShippingAddress()
					// ->setCountryId('')
					// ->setPostcode('')	
					// ->setCollectShippingRates(true);
					// $this->_getQuote()->save();
					// $this->loadLayout()->renderLayout();
				// }
				// return;		
		
		// }
		/////////////////////
		// $name= $this->getRequest()->getPost('name_ship');
		// $value= $this->getRequest()->getPost('value_ship');
		

		
		// //tong hop
		// switch ($name) {
			// case 'select_add':
				// $currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
				// $addresses = $currentCustomer->getAddresses();
				// //Mage::log($addresses);
				
				// foreach($addresses as $add){
					// $current_id=$add->getData('entity_id');
					// //Mage::log($add->getData('entity_id'));
					// if($current_id==$value){
						// $data=$add->getData('country_id');
						// break;
					// }
				// }
				// $this->_getQuote()->getShippingAddress()
				// ->setCountryId($data)
				// ->setCollectShippingRates(true);
				// $this->_getQuote()->save();
				// $this->loadLayout()->renderLayout();
				// return;
			// case 'country':
				// $data=$value;
				// if($data){
				// echo $data;die();
				// $this->_getQuote()->getShippingAddress()
				// ->setCountryId($data)
				// ->setCollectShippingRates(true);
				// $this->_getQuote()->save();
				// $this->loadLayout()->renderLayout();
				// }
				// else{
				 // $this->_getQuote()->getShippingAddress()
				// ->setCountryId('')
				// ->setPostcode('')	
				// ->setCollectShippingRates(true);
				// $this->_getQuote()->save();
				// $this->loadLayout()->renderLayout();
				// }
				// return;
			// case 'zipcode':
				// $this->_getQuote()->getShippingAddress()
				// ->setCountryId('')
				// ->setPostcode($value)
				// ->setCollectShippingRates(true);
				// $this->_getQuote()->save();	
				// $this->loadLayout()->renderLayout();
				// return;
			// case 'city':
				// $this->_getQuote()->getShippingAddress()
				// ->setCity($value)
				// ->setCollectShippingRates(true);
				// $this->_getQuote()->save();	
				// $this->loadLayout()->renderLayout();
				// return;
			// case 'regionid':
				// $this->_getQuote()->getShippingAddress()
				// ->setRegionId($value)
				// ->setCollectShippingRates(true);
				// $this->_getQuote()->save();	
				// $this->loadLayout()->renderLayout();
				// return;
			// case 'region':
				// $this->_getQuote()->getShippingAddress()
				// ->setRegion($value)
				// ->setCollectShippingRates(true);
				// $this->_getQuote()->save();	
				// $this->loadLayout()->renderLayout();
				// return;
		// }	
///////////////////		
		// $shiptype= $this->getRequest()->getPost('ship_type');	//khi select option country
		// $id= $this->getRequest()->getPost('address_id');		//khi select option neu' customer has address
		// $zipcode=$this->getRequest()->getPost('zipcode');		//khi type value for input zipcode
		// //echo $shiptype." and ".$id;die();
		// // if ($id=='null'){
			// // echo "aaaaaaaaaa";die();
		// // }
		// if($shiptype!=null){
			// $data=$shiptype;
		// }	
		// else{
			// if($id!='null'){
				// $currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
				// $addresses = $currentCustomer->getAddresses();
				// //Mage::log($addresses);
				
				// foreach($addresses as $add){
					// $current_id=$add->getData('entity_id');
					// //Mage::log($add->getData('entity_id'));
					// if($current_id==$id){
						// $data=$add->getData('country_id');
						// break;
					// }
				// }
				// //$currentAddr = $addresses[$id];
				// //$data=$currentAddr->getData('country_id');
			// }
		// }
		// if($data){
		 // $this->_getQuote()->getShippingAddress()
			// ->setCountryId($data)
            // //->setPostcode('')
            // // ->setCity('asdwsd')
			// // ->setLastname('fsdfsf ')
			// // ->setFirstname('fsfsdf')
			// // ->setStreet('sdfsf')
            // // ->setRegionId('4')
            // // ->setRegion('sss')
			// // ->setTelephone('23424234')
            // ->setCollectShippingRates(true);
			// $this->_getQuote()->save();
		// }
		// else{
		 // $this->_getQuote()->getShippingAddress()
			// ->setCountryId('')
            // ->setPostcode('')
            // // ->setCity('')
			// // ->setLastname('')
			// // ->setFirstname('')
			// // ->setStreet('')
            // // ->setRegionId('')
            // // ->setRegion('')
			// // ->setTelephone('')
            // ->setCollectShippingRates(true);
			// $this->_getQuote()->save();		
		// }

	// //$shiptype = $this->getRequest()->getPost('ship_type');
	// //$data=array('city'=>'asasd','company'=>'dadad','country_id'=>$shiptype,'firstname'=>'daddd','lastname'=>'dadafsdf','postcode'=>'12345','region_id'=>'4','region'=>'fafsdf','street'=>'sfsfsdf','telephone'=>'0278472384');	
	// // $save=array('country_id'=>$data);
	// // //var_dump($data);die();
	// // $this->getOnepage()->saveshipping($save);
	
	// $this->loadLayout()->renderLayout();
	//}
	public function updateshippingselectAction()
	{
		
	
	}
	public function updatetimepickerAction()
	{	
		
		$dateisnow=$this->getRequest()->getPost('now');
		$starttime=$this->getRequest()->getPost('stime');
		$starray=explode(":",$starttime);
		//$count_stimetominutes=(int)$starray[0]*60+(int)$starray[1];
		$count_stimetominutes=$starray[0]*60+$starray[1];
		$endtime=$this->getRequest()->getPost('etime');
		$etarray=explode(":",$endtime);
		//$count_etimetominutes=(int)$etarray[0]*60+(int)$etarray[1];
		$count_etimetominutes=$etarray[0]*60+$etarray[1];
		
		$count_timenow=date("G", Mage::getModel("core/date")->timestamp(time()))*60+date("i", Mage::getModel("core/date")->timestamp(time()));
		if($dateisnow){
			if($count_timenow>=$count_stimetominutes){
				if($count_timenow<$count_etimetominutes){
					echo date("G", Mage::getModel("core/date")->timestamp(time())).":".date("i", Mage::getModel("core/date")->timestamp(time())) ;		//cho phep in tren timepicker time hien tai
				}
				else{
					echo "";		//time off
				}
			}
			else{
				echo $starttime;	//cho phep in start time
			}
		}
		else
			echo $starttime;//cho phep in start time vi` la ngay hom khac
	}

	public function _getSession(){
		Mage::getSingleton('customer/session');
	}
	public function updateloginAction()
	{
        $email=$this->getRequest()->getPost('email');
		$password=$this->getRequest()->getPost('password');
        if ($this->isCustomerLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        //$session = $this->_getSession();
		// Mage::log($email);
		// Mage::log($password);
        if ($this->getRequest()->isPost()) {
            if (!empty($email) && !empty($password)) {
				try{
					Mage::getSingleton('customer/session')->login($email, $password);
				}catch(Mage_Core_Exception $e){
					//echo "0";
					//return;
				}catch(Exception $e){
					//echo "0";
					//return;
				}
			}
        }
        if (Mage::getSingleton('customer/session')->isLoggedIn()){
			echo "1";
		}
		else{
			echo "0";
		}
	}
	public function forgotpassAction(){
		$email=$this->getRequest()->getPost('email');
		$emailerror="0";
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->setForgottenEmail($email);
                //$this->_getSession()->addError($this->__('Invalid email address'));
                //$this->getResponse()->setRedirect(Mage::getUrl('*/*/forgotpassword'));
				$emailerror="0";
				echo $emailerror;
                return $emailerror;
            }

            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newPassword = $customer->generatePassword();
                    $customer->changePassword($newPassword, false);
                    $customer->sendPasswordReminderEmail();

                    //$this->_getSession()->addSuccess($this->__('A new password was sent'));
					$emailerror="1";
                    //$this->getResponse()->setRedirect(Mage::getUrl('*/*'));
					echo $emailerror;
                    return $emailerror;
                }
                catch (Exception $e){
                   // $this->_getSession()->addError($e->getMessage());
                }
            }
            else {
				$emailerror="2";
                //$this->_getSession()->addError($this->__('This email address was not found in our records'));
                Mage::getSingleton('customer/session')->setForgottenEmail($email);
				echo $emailerror;
				return;
            }
        } else {
			$emailerror="0";
            //$this->_getSession()->addError($this->__('Please enter your email.'));
            //$this->getResponse()->setRedirect(Mage::getUrl('*/*/forgotpassword'));
            echo $emailerror;
			return $emailerror;
        }
	//echo $emailerror;
       // $this->getResponse()->setRedirect(Mage::getUrl('*/*/forgotpassword'));
    
	}	
	public function getOnepage()
     {
         return Mage::getSingleton('checkout/type_onepage');
     }
	
     protected function _getQuote()
		{
         return Mage::getSingleton('checkout/cart')->getQuote();
		}
    	
	//public function testAction()
	//{  
		//echo "aaaa";die();
		// $products=Mage::getModel('catalog/product')->getCollection(); // Lay mang? product
		// $products->addAttributeToFilter('sku','384822');		//them dieu kien loc. Theo sku
		// $products->load();					//du lieu duoc load vao $products
		////var_dump($products->getData());
		// foreach($products as $_prod){				//dua ra tung` phan` du lieu
			// var_dump($_prod->getData());
			// die();
		// }
			// $order=Mage::getModel('sales/order')->load(1);
			// var_dump($order->getData());
			// $order->setData('mw_customercomment','hello onestepcheckout');
			// $order->save();
			//$this->loadLayout();
			//$this->renderLayout();
			
        // $order= Mage::getModel('sales/order')->load(13);
		// var_dump($order->getData());
		
		// $order=Mage::getModel('sales/order')->load($lastOrderId);
			// //var_dump($order->getData());
			// $order->setData('mw_customercomment','hello onestepcheckout');
			// $order->save();	
	//}
	public function updateordermethodAction()
	{
		
		// $session = $this->getOnepage()->getCheckout();
        // $a=$session->getLastSuccessQuoteId();
		// if ($a){
		// echo "1";die();
		// }
		// else
		// echo "0";die();		//kiem tra id quote success cho bit checkout success ko, neu success ->=1
		
		// $data1=$this->getRequest()->getPost('method',array());
		// $data2=$this->getRequest()->getPost('billing',array());
		// $data3=$this->getRequest()->getPost('shipping',array());
		// $data4 = $this->getRequest()->getPost('shipping_method', '');
		// $data5= $this->getRequest()->getPost('payment', array());
		// $data6 = $this->getRequest()->getPost('agreement', array());
		// //$data3	=$this->getRequest()->getParam('payment',array());
		
		
		// echo "<pre>";var_dump($data1);echo "</pre>";
		// echo "<pre>";var_dump($data2);echo "</pre>";
		// echo "<pre>";var_dump($data3);echo "</pre>";
		// echo "<pre>";var_dump($data4);echo "</pre>";
		// echo "<pre>";var_dump($data5);echo "</pre>";
		// echo "<pre>";var_dump($data6);echo "</pre>";
		//die();
		
       
		//var_dump($this->getRequest()->getParam());
		//$result = $this->getOnepage()->saveCheckoutMethod($method);

		
		//$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));	//response ve` trang checkout ->trnh` bat` khung ben phai
		//////////////////////

		
		////////////////////////////////////////////////////////////////////////
		if(!$this->isCustomerLoggedIn()){
		$isguest=$this->getRequest()->getPost('register_new_account');
		//echo $isguest;die();
		
			if($isguest=='1' or Mage::helper('onestepcheckout')->haveProductDownloadable()){ //neu checkbox regiter_new_accoutn dc check or co san pham downloadable thi tao new acc 
					$result_save_method = $this->getOnepage()->saveCheckoutMethod('register');
				}
			else{
					$result_save_method = $this->getOnepage()->saveCheckoutMethod('guest');	//save method
				}
		}
		
		/////////////////
        if ($this->getRequest()->isPost()) {
            //$postData = $this->getRequest()->getPost('billing', array());
            //$data = $this->_filterPostData($postData);
			if(!Mage::getStoreConfig('onestepcheckout/config/is_disable'))
				$data_save_billing =$this->filterdata($this->getRequest()->getPost('billing', array()));	//khi chua login
			else
				$data_save_billing = $this->getRequest()->getPost('billing', array());	//khi chua login
			$customerAddressId = $this->getRequest()->getPost('billing_address_id', false); //khi customer login va co address
			if (isset($data_save_billing['email'])) {
				$data_save_billing['email'] = trim($data_save_billing['email']);
			}	
			if($this->getRequest()->getPost('subscribe_newsletter')=='1'){
				if($this->isCustomerLoggedIn()){
					$customer = Mage::getSingleton('customer/session')->getCustomer();
					$customer->setIsSubscribed(1);
				}else{
					$this->savesubscibe($data_save_billing['email']);
				}
			}
			//$this->filterdata($data_save_billing);die();	
			//$this->filterdata( $this->getRequest()->getPost('billing', array()));die();	
			
			$result_save_billing = $this->getOnepage()->saveBilling($data_save_billing, $customerAddressId);		//save billing
		}
		
		///////////////
		$isclick=$this->getRequest()->getPost('ship_to_same_address');
		$ship="billing";
		if(!$isclick=='1'){
				$ship="shipping";
		}
		if ($this->getrequest()->ispost()) {
			if(!Mage::getStoreConfig('onestepcheckout/config/is_disable'))
				$data_save_shipping = $this->filterdata($this->getrequest()->getpost($ship, array()));
			else
				$data_save_shipping = $this->getrequest()->getpost($ship, array());
			 //$customeraddressid	 =$data_save_shipping['address_id'];
			$customeraddressid = $this->getrequest()->getpost($ship.'_address_id', false);
			$result_save_shipping = $this->getonepage()->saveshipping($data_save_shipping, $customeraddressid);		//save shipping
		}

		///////////////
		
		if ($this->getRequest()->isPost()) {
			$data_save_shipping_method = $this->getRequest()->getPost('shipping_method', '');	
			$result_save_shipping_method = $this->getOnepage()->saveShippingMethod($data_save_shipping_method);				//save shipping_method
			if(!$result_save_shipping_method) {		//cho phep save gift message
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request'=>$this->getRequest(), 'quote'=>$this->getOnepage()->getQuote()));
			}
		}
		
		///////////////
		
		$result_savepayment = array();
		$data_savepayment = $this->getRequest()->getPost('payment', array());
		$result_savepayment = $this->getOnepage()->savePayment($data_savepayment);				//save payment
		$redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
		if(isset($redirectUrl))
		{
			$this->_redirectUrl($redirectUrl);
			return;
		}		
		////////////////
		
		//parent::saveOrderAction();
		 $result_order = array();
        
             // if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                 // $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                 // if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                     // $result['success'] = false;
                     // $result['error'] = true;
                     // $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                     // $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                     // return;
                 // }
             // }

            if ($data_order = $this->getRequest()->getPost('payment', false)) {
                 $this->getOnepage()->getQuote()->getPayment()->importData($data_order);
             }
            try{	//kiem tra xem cac info nhu info payment co' phai? hop le hay la` gian lan, neu ko thi se bat loi~ =catch va` redirect
			$this->getOnepage()->saveOrder();
            }
	        catch (Exception $e) {
             				echo $e->getMessage();
             				echo "<script type='text/javascript'>setTimeout('window.location=\"".Mage::getUrl('checkout/onepage')."\"',2000)</script>";//cho` 2 s de khach hang nhin thay loi va redirect
             				return;//return giup thoat ra va ko thuc hien cac lenh con lai, neu thuc hien tiep se gay loi
            }
			$session = $this->getOnepage()->getCheckout();
			$lastOrderId = $session->getLastOrderId();
			$data_customercomment ="";
			
			if ($this->getrequest()->ispost()) {
				$data_customercomment = $this->getrequest()->getpost('onestepcheckout_comments');
				//$order=Mage::getModel('sales/order')->load($lastOrderId);	//load record co $lastOrderId, va insert mw_customercomment
				//$order->setData('mw_customercomment',$data_customercomment);
				
				$Deliverystatus= $this->getrequest()->getpost('deliverydate');
				$Deliverydate = $this->getrequest()->getpost('onestepcheckout_date');
				$Deliverytime= $this->getrequest()->getpost('onestepcheckout_time');

				$order=Mage::getModel('onestepcheckout/onestepcheckout');//->getCollection()->getData();
				//echo'<pre>';var_dump($order);die();
				$order->setSalesOrderId($lastOrderId);
				$order->setMwCustomercommentInfo($data_customercomment);
				if($Deliverystatus=="late"){				
					$order->setMwDeliverydateDate($Deliverydate);
					$order->setMwDeliverydateTime($Deliverytime);
				}
				$order->save();
			}
			 //$this->getOnepage()->getQuote()->save();			
			$redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
			$result_order['success'] = true;
			$result_order['error']   = false;
			$cart = Mage::getModel('checkout/cart');	//fix khi checkout song ma` cart van con san pham
			$cartItems = $cart->getItems(); 
			foreach ($cartItems as $item){
				$cart->removeItem($item->getId())->save();
			}
			if(isset($redirectUrl))
			{
				$this->_redirectUrl($redirectUrl);
				return;
			}	
			$this->_redirect('checkout/onepage/success');
			
		//////////////
	}
	
	protected function filterdata($data,$filter="true")
	{
			
		$arrayname=array('address_id','firstname','lastname','company','email','city','region_id','region','postcode','country_id','telephone','fax','save_in_address_book');
		$filterdata=array();
		if(version_compare(Mage::getVersion(),'1.4.2.0','>=')){
			$filterdata=array(
				'address_id'=>'n/a',
				'firstname'=>'n/a',
				'lastname'=>'n/a',
				'company'=>'n/a',
				'email'=>'n/a@na.na',
				'street'=>array('n/a','n/a'),
				'city'=>'n/a',
				'region_id'=>'n/a',
				'region'=>'n/a'	,	
				'postcode'=>'n/a',
				'country_id'=>'n/a',
				'telephone'=>'n/a',
				'fax'=>'n/a',
				'save_in_address_book'=>'0'
			);
		}
		else{
			$filterdata=array(
				'address_id'=>'n/a',
				'firstname'=>'n/a',
				'lastname'=>'n/a',
				'company'=>'n/a',
				'email'=>'n/a',
				'street'=>array(
							'street1'=>'street is null',
							'street2'=>'street is null'
						),
				'city'=>'n/a',
				'region_id'=>'n/a',
				'region'=>'n/a'	,	
				'postcode'=>'n/a',
				'country_id'=>'n/a',
				'telephone'=>'n/a',
				'fax'=>'n/a',
				'save_in_address_book'=>'0'
			)	;	
		}
		foreach($data as $item=>$value){
			if(!is_array($value)){
					//if($value)	//lenh chuoc day gay loi save addressbook mac du value cua saveaddressbook =0
					if($value!='') // fix loi save address book voi saveaddressbook co value=0
						$filterdata[$item]=$value;
			}
			else{
				$street=$value;
				if($street[0])
					$filterdata[$item]=array($street[0],$street[1]);
			}
		}
		
//		foreach($arrayname as $name){
//			if(!$data[$name]==null )
//				{
//						$data[$name] = "n/a";
//				}
//		}
//		if($data['street']){
//			if(!$data['street'][0])
//				$data['street'][0]='n/a';
//		}
//		else {
//			$data['street']=array('n/a','n/a');
//		}
		return $filterdata;
//	foreach($filterdata as $item=>$value){
//		echo $item."=".$value."<br>";
//		}
//		echo '<pre>';var_dump($filterdata['street']);
//		die();
	}
	
    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
    public function getGeoip(){
					//require_once 'MW/Onestepcheckout/Helper/Geoip/Get_add.php';//cach 1: dung` file database ngay tren host    	
					//Mage::getSingleton('core/session')->setIp($ip);
					//Mage::getSingleton('core/session')->setCountrycode($country_code);	//luu vao session code country		
			    	//cach 2 voi geo ip la dung API ho tro
			    	
			
					//$timestamp=$date->get(Zend_Date::TIMESTAMP)+$date->get(Zend_Date::TIMEZONE_SECS);
			
					//0:status(true/false) 1:ip 2:CountryCode 3:CountryName 4:RegionCode  
					//5:RegionName  6:City  7:ZipCode 8:Latitude 9:Longitude	
					if(Mage::getStoreConfig('onestepcheckout/config/enable_geoip')){
					    	try {
							$key="718b4c6e9d4b09b15cbb35e846457723cd0fe7d842401c7203069b7252f7d289";
							//link==>  http://api.ipinfodb.com/v2/ip_query.php?key=718b4c6e9d4b09b15cbb35e846457723cd0fe7d842401c7203069b7252f7d289&ip=184.168.193.20&timezone=true
					 		$date= Mage::app()->getLocale()->date();   		
							$timezone_server=$date->get(Zend_Date::TIMEZONE_SECS)/3600;
							$timezone_client=$timezone_server;     		
								//$xml = simplexml_load_file('http://freegeoip.appspot.com/xml/'.$_SERVER['REMOTE_ADDR']);
								$xml = simplexml_load_file('http://api.ipinfodb.com/v2/ip_query.php?key='.$key."&ip=".$_SERVER['REMOTE_ADDR']."&timezone=true");
								// echo $xml->getName() . "<br />";
									// foreach($xml->children() as $child)
									  // {
									  // echo $child->getName() . ": " . $child . "</br>";
									  // }
									// echo "<br>";
								if(!$xml)
									return false;
								$info_ip_address=$xml->children();
								//echo $info_ip_address[2];die(); 
								if($info_ip_address[0]){
										$longitude=$info_ip_address[9];
										//$longitude=-25;
										$hourpart=(float)$longitude/15;
										$off=$hourpart-(int)$hourpart;
										
										$timezone_client=(int)$hourpart;
										
										if(abs($off) >0.5){
												$timezone_client=((int)$hourpart>0)?(int)$hourpart+1:(int)$hourpart-1;
											}
										Mage::register('Countrycode',$info_ip_address[1]);
										Mage::register('Countryname',$info_ip_address[2]);
										Mage::register('Regioncode',$info_ip_address[3]);
										Mage::register('Regionname',$info_ip_address[4]);
										Mage::register('City',$info_ip_address[5]);
										Mage::register('Zipcode',$info_ip_address[6]);
										Mage::register('Latitude',$info_ip_address[7]);
										Mage::register('Timezoneclient',$timezone_client);
										Mage::register('Timezoneserver',$timezone_server);
										//var_dump(Mage::helper('directory')->getRegionJson());die(); //return string container info region id
						        		$statesCollection = Mage::getModel('directory/region')->getResourceCollection()	//get info region with country 
						                   	 	->addCountryFilter($info_ip_address[2])
						                    	//->addCountryFilter('US')
						        				->load();
						               	//$items = array();
						               	//$i=0;
						               	//$regioncode="";
						       			foreach ($statesCollection as $state) {
						                    if (!$state->getRegionId()) {
						                        continue;
						                    }
						                	//$items[$state->getRegionId()] = $state->getCode();//$state->getName()
						       				if($state->getCode()==$info_ip_address[3]){
						                	//if($state->getCode()=='AZ'){
							       				//Mage::register('Regionid',$state->getRegionId());
							       				Mage::register('Regionid',$state->getRegionId());
							       				//$regioncode=$state->getRegionId();
							       				break;
						       				}
						       				//$i++;
						       			} 
						       			//echo $i."<pre>";var_dump($regioncode);die(); 
								}
								//echo $timezone;	
							} catch (Exception $e) {
							 	//echo "net die";die();
							}
					
					}//	end func geoip		
    }
	public function savesubscibe($mail)
    {
        if ($mail) {
            //$session            = Mage::getSingleton('core/session');
			$session            = Mage::getSingleton('checkout/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = (string) $mail;

            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && 
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::getUrl('customer/account/create/')));
                }

                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($this->__('Sorry, but your can not subscribe email adress assigned to another user.'));
                }

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $session->addSuccess($this->__('Confirmation request has been sent.'));
                }
                else {
                    $session->addSuccess($this->__('Thank you for your subscription.'));
                }
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription.'));
            }
        }
        //$this->_redirectReferer();
    }
}