<?php

declare(strict_types=1);

namespace X247Commerce\Sales\Plugin;

use X247Commerce\StoreLocatorSource\Helper\User as UserHelper;
use Magento\Backend\Model\Auth\Session;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class AfterCalculateSalesDashboard
{
    protected $_adminSession;

    protected $locatorSourceResolver;

    protected $userHelper;

    public function __construct(
        Session $adminSession,
        LocatorSourceResolver $locatorSourceResolver,
        UserHelper $userHelper
    ) {
        $this->_adminSession = $adminSession;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->userHelper = $userHelper;
    }

    public function afterCalculateSales(
        \Magento\Reports\Model\ResourceModel\Order\Collection $subject,
        $result
    ) {
        $user = $this->_adminSession->getUser();
        $isStaffUser = $this->userHelper->isStaffUser($user);

        $amLocatorStoresByUser = $this->locatorSourceResolver->getAmLocatorStoresByUser($user);

        if ($isStaffUser) {
            $result->getSelect()
                    ->joinleft(['slsso' => 'sales_order'], 'main_table.entity_id=slsso.entity_id', [])
                    ->where('slsso.store_location_id IN (?)', $amLocatorStoresByUser);
        }
        return $result;
    }

    public function afterCalculateTotals(
        \Magento\Reports\Model\ResourceModel\Order\Collection $subject,
        $result
    ) {
        $user = $this->_adminSession->getUser();
        $isStaffUser = $this->userHelper->isStaffUser($user);

        $amLocatorStoresByUser = $this->locatorSourceResolver->getAmLocatorStoresByUser($user);

        if ($isStaffUser) {
            $result->getSelect()
                ->join(['slsso' => 'sales_order'], 'main_table.entity_id=slsso.entity_id', [])
                ->where('slsso.store_location_id IN (?)', $amLocatorStoresByUser);
        }
        return $result;
    }

    public function afterJoinCustomerName(
        \Magento\Reports\Model\ResourceModel\Order\Collection $subject,
        $result
    ) {

        $user = $this->_adminSession->getUser();
        $isStaffUser = $this->userHelper->isStaffUser($user);

        $amLocatorStoresByUser = $this->locatorSourceResolver->getAmLocatorStoresByUser($user);

        if ($isStaffUser) {
            $result->getSelect()
                    ->joinleft(['slsso' => 'sales_order'], 'main_table.entity_id=slsso.entity_id', [])
                    ->where('slsso.store_location_id IN (?)', $amLocatorStoresByUser);
        }
        return $result;
    }
}
