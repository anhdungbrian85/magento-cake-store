<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace X247Commerce\Sales\Model\ResourceModel\Transaction\Grid;

use X247Commerce\StoreLocatorSource\Helper\User as UserHelper;
use Magento\Backend\Model\Auth\Session;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Transaction\Grid\Collection
{   

    protected $_adminSession;
    protected $locatorSourceResolver;
    protected UserHelper $userHelper;

    public function __construct(
        Session $adminSession,
        LocatorSourceResolver $locatorSourceResolver,
        UserHelper $userHelper,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Registry $registryManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_adminSession = $adminSession;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->userHelper = $userHelper;
        parent::__construct(
            $entityFactory, 
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $registryManager,
            $connection,
            $resource
        );
    }


    /**
     * Resource initialization
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $transactions = parent::_initSelect();
        $user = $this->_adminSession->getUser();
        $isStaffUser = $this->userHelper->isStaffUser($user);

        $amLocatorStoresByUser = $this->locatorSourceResolver->getAmLocatorStoresByUser($user);

        if ($isStaffUser) {
            $transactions->getSelect()
                    ->joinleft(['xso' => 'sales_order'], 'main_table.order_id=xso.entity_id', [])
                    ->where('xso.store_location_id IN (?)', $amLocatorStoresByUser);
        }
        return $transactions;
    }
}
