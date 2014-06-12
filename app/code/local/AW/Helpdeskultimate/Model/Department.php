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

class AW_Helpdeskultimate_Model_Department extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('helpdeskultimate/department');
    }

    public function getAddress()
    {
        if ($this->getEmail()) {
            return $this->getEmail();
        } else {
            return Mage::getStoreConfig('trans_email/ident_' . $this->getContact() . '/email');
        }
    }

    public function getSenderAddress()
    {
        return Mage::helper('helpdeskultimate')->getIdentityEmail($this->getSender());
    }


    /**
     * Loads department by contact/email
     * @param object $mail
     * @return
     */
    public function loadByContact($mail)
    {
        return $this->load($mail, 'email');
    }

    /**
     * Loads by primary store id
     * @param object $id [optional]
     * @return
     */
    public function loadByPrimaryStoreId($id = null)
    {
        return $this->load($id, 'primary_store_id');
    }


    public function loadStats($storeId = 0)
    {
        $tickets = Mage::getModel('helpdeskultimate/ticket')->getCollection();
        $tt = Mage::getResourceModel('helpdeskultimate/ticket')->getTable('helpdeskultimate/ticket');
        if (is_array(Mage::registry('aw_hdu_departments_fromto'))) {
            $ft = Mage::registry('aw_hdu_departments_fromto');
            $dq = " AND (%s.created_time>='{$ft[0]}' AND %s.created_time<='{$ft[1]}')";
        } else {
            $dq = "";
        }

        $select = $tickets->getSelect();
        $select
            ->having('department_id=?', $this->getId())
            ->joinLeft(
                array('st' => $tt),
                'st.id=main_table.id' . sprintf($dq, 'st', 'st'),
                array('total_count' => 'COUNT(st.id)')
            )
            ->joinLeft(
                array('so' => $tt),
                'so.id=main_table.id AND so.status=1' . sprintf($dq, 'so', 'so'),
                array('open_count' => 'COUNT(so.id)')
            )
            ->joinLeft(
                array('sc' => $tt),
                'sc.id=main_table.id AND sc.status=2' . sprintf($dq, 'sc', 'sc'),
                array('closed_count' => 'COUNT(sc.id)')
            )
            ->joinLeft(
                array('sw' => $tt),
                'sw.id=main_table.id AND sw.status=3' . sprintf($dq, 'sw', 'sw'),
                array('waiting_count' => 'COUNT(sw.id)')
            )
            ->group('main_table.department_id');

        if ($storeId) {
            $select->where('main_table.store_id=?', $storeId);
        }

        $tickets->load();
        $this
            ->setTotalCount(0)
            ->setOpenCount(0)
            ->setClosedCount(0)
            ->setWaitingCount(0);
        foreach ($tickets as $item) {
            $this
                ->setTotalCount($item->getTotalCount())
                ->setOpenCount($item->getOpenCount())
                ->setClosedCount($item->getClosedCount())
                ->setWaitingCount($item->getWaitingCount());
            return $this;
        }
        return $this;
    }

    /**
     * Indices if department is allowed to check all gateways
     * @return bool
     */
    public function usesAllGateways()
    {
        return array_search(0, $this->getGateways()) !== false;
    }

    /**
     * Retrives department name
     * If department not defined name is 'General Department'
     * @return string
     */
    public function getName()
    {
        if ($depName = $this->getData('name')) {
            return $depName;
        }
        return Mage::helper('helpdeskultimate')->__('General Department');
    }

    /**
     * Returns assigned gateways
     * @return array
     */
    public function getGateways()
    {
        if (is_array($this->getData('gateways'))) {
            return $this->getData('gateways');
        }
        return explode(',', $this->getData('gateways'));
    }

    /**
     * Returns firs applicable gateway for this department
     * @return
     */
    public function getMainGateway()
    {
        //return first active gateway
        foreach (Mage::getModel('helpdeskultimate/gateway')->getCollection()->addActiveFilter() as $gw) {
            if ($this->usesAllGateways()) {
                return $gw;
            } else {
                if (array_search($gw->getId(), $this->getGateways()) !== false) {
                    return $gw;
                }
            }
        }
        return false;
    }

    /**
     * Unserialize gateways
     * @return
     */
    protected function _afterLoad()
    {
        $gateways = explode(',', $this->getData('gateways'));
        $this->setData('gateways', $gateways);
        $visibleOn = explode(',', $this->getData('visible_on'));
        $this->setData('visible_on', $visibleOn);
        return parent::_afterLoad();
    }

    /**
     * Serialize gateways
     * @return
     */
    protected function _beforeSave()
    {
        if (is_array($this->getData('gateways'))) {
            $gateways = implode(',', $this->getData('gateways'));
            $this->setData('gateways', $gateways);
        }
        if (is_array($this->getData('visible_on'))) {
            $visibleOn = implode(',', $this->getData('visible_on'));
            $this->setData('visible_on', $visibleOn);
        }
        $email = strtolower($this->getEmail());
        $this->setEmail($email)->setContact($email);

        if (is_array($this->getData('allowed_roles'))) {
            $allowedRoles = $this->getData('allowed_roles');
        } else {
            $allowedRoles = array();
        }
        Mage::register('aw_hdu_allowed_roles_value', $allowedRoles);

        return parent::_beforeSave();
    }

    /**
     * Save department permissions
     * @return
     */
    protected function _afterSave()
    {
        $allowedRoles = Mage::registry('aw_hdu_allowed_roles_value');

        $roles = Mage::getModel('admin/role')->getCollection()->setRolesFilter()->getAllIds();
        foreach ($roles as $roleId) {
            $departmentPermission = Mage::getModel('helpdeskultimate/department_permissions')->loadByRoleId($roleId);
            $value = $departmentPermission->getValue();
            if (in_array($roleId, $allowedRoles)) {
                if (!in_array($this->getId(), $value)) {
                    array_push($value, $this->getId());
                }
            } else {
                $value = array_diff($value, array($this->getId()));
            }
            $departmentPermission->addData(array('role_id' => $roleId, 'value' => $value));
            try {
                $departmentPermission->save();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return parent::_afterSave();
    }

    public function getDepEmails()
    {
        return $this->getCollection()->getColumnValues('email');
    }
}