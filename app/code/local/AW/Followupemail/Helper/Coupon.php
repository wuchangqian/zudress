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
 * @package    AW_Followupemail
 * @version    3.5.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Followupemail_Helper_Coupon extends Mage_Core_Helper_Abstract
{
    const MYSQL_DATETIME_FORMAT = 'Y-m-d';

    public function isFUECoupon($couponCode)
    {
        $collection = Mage::getModel('salesrule/coupon')->getCollection();
        $collection->getSelect()->joinLeft(
            array('scr' => $collection->getTable('salesrule/rule')),
            'main_table.rule_id = scr.rule_id',
            array('coupon_type'))
            ->where('scr.coupon_type = ?', Mage::helper('followupemail/coupon')->getFUECouponsCode())
            ->where('main_table.code = ?', $couponCode);
        return $collection->getSize() ? true : false;
    }

    public static function generateCode($ruleId, $prefix = '')
    {
        $firstPass = TRUE;
        $coupon = Mage::getModel('followupemail/coupons');
        while ($firstPass || $coupon->getData()) {
            $uniqueCode = $prefix . dechex($ruleId) . 'X' . strtoupper(uniqid());
            $coupon->fueLoadByCode($uniqueCode);
            $firstPass = FALSE;
        }
        return $uniqueCode;
    }

    public static function createNew($rule, $startFrom = null)
    {
        $date = new Zend_Date();
        $date->addDay((int)$rule->getCouponExpireDays());
        $date->setHour(0)->setMinute(0)->setSecond(0);
        if (!is_null($startFrom)) {
            $date->addDay((int)$startFrom);
        }
        $coupon = Mage::getModel('followupemail/coupons');
        $salesRule = Mage::getModel('salesrule/rule')->load($rule->getCouponSalesRuleId());
        if ($salesRule->getData()) {
            $_usagePerCustomer = $salesRule->getUsesPerCustomer() && is_numeric($salesRule->getUsesPerCustomer()) ? $salesRule->getUsesPerCustomer() : 1;
            $coupon->setRuleId($rule->getCouponSalesRuleId())
                ->setExpirationDate($date)
                ->setCode(Mage::helper('followupemail/coupon')->generateCode($rule->getId(), $rule->getCouponPrefix()))
                ->setUsagePerCustomer($_usagePerCustomer)
                ->setUsageLimit($_usagePerCustomer);
            $coupon->save();
        }
        return $coupon;
    }

    public static function getFUECouponsCode()
    {
        return Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO;
    }

    public function canUseCoupons()
    {
        return Mage::helper('followupemail')->checkVersion('1.4.1');
    }
}
