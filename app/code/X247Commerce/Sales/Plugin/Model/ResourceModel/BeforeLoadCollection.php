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
            $deliveryDateSql = "CONCAT(DATE_FORMAT(`ad`.`date`, '%Y-%m-%d'), ' ', ad.time,':00:00')";
            $pickupDateSql =  "CONCAT(DATE_FORMAT(ap.date, '%Y-%m-%d'), ' ', FROM_UNIXTIME(ap.time_from, '%H:%i:%s'))";
            $subject->getSelect()
                ->joinLeft(['ad' => $subject->getTable('amasty_amcheckout_delivery')],
                        'main_table.entity_id=ad.order_id AND ad.date is not null AND ad.time is not null',
                        ['ad.date as delivery_date', 'ad.time as delivery_time'])
                ->joinLeft(['ap' => $subject->getTable('amasty_storepickup_order')],
                        'main_table.entity_id=ap.order_id AND ap.date is not null AND ap.time_from is not null',
                        ['ap.date as pickup_date', 'ap.time_from as pickup_time_from', 'ap.time_to as pickup_time_to' ])
                ->joinLeft(['mpt' => $subject->getTable('sales_order_payment')],
                        'main_table.entity_id=mpt.parent_id', ['mpt.additional_information'])
                ->columns(["colection_delivery_date" => new \Zend_Db_Expr("(CASE WHEN `ap`.`date` IS NULL THEN $deliveryDateSql ELSE $pickupDateSql END)")]);
        }

        return [$printQuery, $logQuery];
    }

}
