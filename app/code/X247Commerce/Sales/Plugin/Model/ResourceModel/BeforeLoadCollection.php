<?php

namespace X247Commerce\Sales\Plugin\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class BeforeLoadCollection
{
    /**
     * @param Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     * @throws LocalizedException
     */
    protected $userHelper;
    protected $adminSession;

    public function __construct(
        \X247Commerce\StoreLocatorSource\Helper\User $userHelper,
        \Magento\Backend\Model\Auth\Session $adminSession,
    ) {
        $this->userHelper = $userHelper;
        $this->adminSession = $adminSession;
    }

    public function beforeLoad(Collection $subject, bool $printQuery = false, bool $logQuery = false): array
    {
        if (!$subject->isLoaded()) {

            $subject->getSelect()->joinleft(['ad' => $subject->getTable('amasty_amcheckout_delivery')], 
                        'main_table.entity_id=ad.order_id', ['ad.date as delivary_date', 
                        'ad.time as delivary_time'])
            ->joinleft(['ap' => $subject->getTable('amasty_storepickup_order')], 
                        'main_table.entity_id=ap.order_id', 
                        ['ap.date as pickup_date', 'ap.time_from as pickup_time_from', 'ap.time_to as pickup_time_to' ])
            ->joinleft(['mpt' => $subject->getTable('sales_order_payment')], 
                        'main_table.entity_id=mpt.parent_id', ['mpt.additional_information']);
        }

        return [$printQuery, $logQuery];
    }

}