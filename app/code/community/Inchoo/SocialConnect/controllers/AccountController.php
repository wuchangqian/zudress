<?php
/**
* Inchoo
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
* Please do not edit or add to this file if you wish to upgrade
* Magento or this extension to newer versions in the future.
** Inchoo *give their best to conform to
* "non-obtrusive, best Magento practices" style of coding.
* However,* Inchoo *guarantee functional accuracy of
* specific extension behavior. Additionally we take no responsibility
* for any possible issue(s) resulting from extension usage.
* We reserve the full right not to provide any kind of support for our free extensions.
* Thank you for your understanding.
*
* @category Inchoo
* @package SocialConnect
* @author Marko Martinović <marko.martinovic@inchoo.net>
* @copyright Copyright (c) Inchoo (http://inchoo.net/)
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*/

class Inchoo_SocialConnect_AccountController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return $this;
        }

        /*
         * Avoid situations where before_auth_url redirects when doing connect
         * and disconnect from account dashboard. Authenticate.
         */
        if (!Mage::getSingleton('customer/session')
                ->unsBeforeAuthUrl()
                ->unsAfterAuthUrl()
                ->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }

    }

    public function googleAction()
    {
        $userInfo = Mage::getSingleton('inchoo_socialconnect/google_info_user')
                ->load();

        Mage::register('inchoo_socialconnect_google_userinfo', $userInfo);

        $this->loadLayout();
        $this->renderLayout();
    }

    public function facebookAction()
    {
        $userInfo = Mage::getSingleton('inchoo_socialconnect/facebook_info_user')
            ->load();

        Mage::register('inchoo_socialconnect_facebook_userinfo', $userInfo);

        $this->loadLayout();
        $this->renderLayout();
    }

    public function twitterAction()
    {
        // Cache user info inside customer session due to Twitter window frame rate limits
        if(!($userInfo = Mage::getSingleton('customer/session')
                ->getInchooSocialconnectTwitterUserinfo()) || !$userInfo->hasData()) {
            
            $userInfo = Mage::getSingleton('inchoo_socialconnect/twitter_info_user')
                ->load();

            Mage::getSingleton('customer/session')
                ->setInchooSocialconnectTwitterUserinfo($userInfo);
        }

        Mage::register('inchoo_socialconnect_twitter_userinfo', $userInfo);

        $this->loadLayout();
        $this->renderLayout();
    }

    public function linkedinAction()
    {
        $userInfo = Mage::getSingleton('inchoo_socialconnect/linkedin_info_user')
            ->load();

        Mage::register('inchoo_socialconnect_linkedin_userinfo', $userInfo);

        $this->loadLayout();
        $this->renderLayout();
    }

}
