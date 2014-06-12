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

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Helpdeskultimate
 * @copyright  Copyright (c) 2008-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 * @version    1.0
 */

class AW_Helpdeskultimate_Model_Popmessage extends AW_Core_Model_Abstract
{
    const DB_FIELD_HASH = 'hash';
    const DB_FIELD_UID = 'uid';

    protected $_customer;

    protected function _construct()
    {
        $this->_init('helpdeskultimate/popmessage');
    }

    /**
     * Loads message by hash
     * @param string $hash
     * @return AW_Helpdeskultimate_Model_Popmessage
     */
    public function loadByHash($hash)
    {
        return parent::load($hash, self::DB_FIELD_HASH);
    }

    /**
     * Tests message for validity
     * @return
     */
    public function test()
    {
        if ($this->getId()) {
            // Already exists
            $entity = Mage::getModel('helpdeskultimate/popmessage')->loadByHash($this->_createHash())->getId();
            $exists = $entity->getId() != $this->getId();
        } else {
            //new record
            $exists = !!Mage::getModel('helpdeskultimate/popmessage')->loadByHash($this->_createHash())->getId();
        }
        return !$exists;
    }

    /**
     * Loads message bu UID
     *
     * @param string $id
     * @return AW_Helpdeskultimate_Model_Popmessage
     */
    public function loadByUid($uid)
    {
        // Loads PopMessage by uid
        return parent::load($uid, self::DB_FIELD_UID);
    }

    /**
     * Creates hash from subject,from and body
     * @return string
     */
    protected function _createHash()
    {
        $str = implode('|', array(
            $this->getFrom(),
            $this->getSubject(),
            $this->getBody(),
        ));
        return md5($str);
    }

    /**
     * Creates hash if no hash
     * @return
     */
    public function _beforeSave()
    {
        if (!$this->getHash()) {
            // Generate UID if no specified
            $this->setHash($this->_createHash());
        }
        return parent::_beforeSave();
    }

    public function getDataForProto()
    {
        $protoData = array(
            'subject'      => ($this->getSubject()) ? $this->getSubject() : 'no subject',
            'content_type' => ($this->getContentType()) ? $this->getContentType() : '',
            'from'         => $this->getFrom(),
            'content'      => $this->getBody(),
            'gateway_id'   => $this->getGatewayId(),
            'source'       => 'email'
        );
        return $protoData;
    }
}

