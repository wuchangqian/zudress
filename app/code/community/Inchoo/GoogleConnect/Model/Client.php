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
* @package GoogleConnect
* @author Marko Martinović <marko.martinovic@inchoo.net>
* @copyright Copyright (c) Inchoo (http://inchoo.net/)
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*/

require_once(Mage::getBaseDir('lib') . '/GoogleApiPhpClient/Google_Client.php');
require_once(Mage::getBaseDir('lib') . '/GoogleApiPhpClient/contrib/Google_Oauth2Service.php');

class Inchoo_GoogleConnect_Model_Client
{
    const APPLICATION_NAME = 'inchoo-googleconnect';
    const REDIRECT_URI_ROUTE = 'googleconnect/index/connect';

    const XML_PATH_ENABLED = 'customer/inchoo_googleconnect/enabled';
    const XML_PATH_CLIENT_ID = 'customer/inchoo_googleconnect/client_id';
    const XML_PATH_CLIENT_SECRET = 'customer/inchoo_googleconnect/client_secret';

    protected $client = null;
    protected $oauth2 = null;
    
    public function __construct() {
        $enabled = $this->_isEnabled();
        $clientId = $this->_getClientId();
        $clientSecret = $this->_getClientSecret();

        if(!empty($enabled)) {
            $this->client = new Google_Client();
            $this->client->setAccessType('offline');
            $this->client->setApplicationName(self::APPLICATION_NAME);
            $this->client->setClientId($clientId);
            $this->client->setClientSecret($clientSecret);
            $this->client->setRedirectUri(
                Mage::getModel('core/url')->sessionUrlVar(
                        Mage::getUrl(self::REDIRECT_URI_ROUTE)
                    )
                );

            $this->oauth2 = new Google_Oauth2Service($this->client);
        }
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getOauth2()
    {
        return $this->oauth2;
    }

    protected function _isEnabled()
    {
        return $this->_getStoreConfig(self::XML_PATH_ENABLED);
    }

    protected function _getClientId()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_ID);
    }

    protected function _getClientSecret()
    {
        return $this->_getStoreConfig(self::XML_PATH_CLIENT_SECRET);
    }

    protected function _getStoreConfig($xmlPath)
    {
        return Mage::getStoreConfig($xmlPath, Mage::app()->getStore()->getId());
    }

}