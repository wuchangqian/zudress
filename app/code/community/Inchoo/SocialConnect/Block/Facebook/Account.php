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

class Inchoo_SocialConnect_Block_Facebook_Account extends Mage_Core_Block_Template
{
    /**
     *
     * @var Inchoo_SocialConnect_Model_Facebook_Oauth2_Client
     */
    protected $client = null;

    /**
     *
     * @var Inchoo_SocialConnect_Model_Facebook_Info_User
     */
    protected $userInfo = null;

    protected function _construct() {
        parent::_construct();

        $this->client = Mage::getSingleton('inchoo_socialconnect/facebook_oauth2_client');
        if(!($this->client->isEnabled())) {
            return;
        }

        $this->userInfo = Mage::registry('inchoo_socialconnect_facebook_userinfo');

        $this->setTemplate('inchoo/socialconnect/facebook/account.phtml');
    }
    
    protected function _hasData()
    {
        return $this->userInfo->hasData();
    }    

    protected function _getFacebookId()
    {
        return $this->userInfo->getId();
    }

    protected function _getStatus()
    {
        if($this->userInfo->getLink()) {
            $link = '<a href="'.$this->userInfo->getLink().'" target="_blank">'.
                    $this->htmlEscape($this->userInfo->getName()).'</a>';
        } else {
            $link = $this->userInfo->getName();
        }

        return $link;
    }

    protected function _getEmail()
    {
        return $this->userInfo->getEmail();
    }

    protected function _getPicture()
    {
        if($this->userInfo->getPicture()) {
            return Mage::helper('inchoo_socialconnect/facebook')
                    ->getProperDimensionsPictureUrl($this->userInfo->getId(),
                            $this->userInfo->getPicture()->data->url);
        }

        return null;
    }

    protected function _getName()
    {
        return $this->userInfo->getName();
    }

    protected function _getGender()
    {
        $_tmp = $this->userInfo->getGender();
        if(!empty($_tmp)) {
            return ucfirst($this->userInfo->getGender());
        }

        return null;
    }

    protected function _getBirthday()
    {
        $_tmp = $this->userInfo->getBirthday();
        if(!empty($_tmp)) {
            $birthday = date('F j, Y', strtotime($this->userInfo->getBirthday()));
            return $birthday;
        }

        return null;
    }

}