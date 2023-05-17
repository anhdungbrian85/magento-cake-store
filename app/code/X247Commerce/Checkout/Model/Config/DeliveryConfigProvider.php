<?php
declare(strict_types=1);

namespace X247Commerce\Checkout\Model\Config;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\CheckoutDeliveryDate\Model\ConfigProvider;
use Magento\Store\Model\ScopeInterface;

class DeliveryConfigProvider extends ConfigProvider
{
    public const WEEKDAY_DELIVERY_TIMESLOT = '16:00 - 20:00';
    public const WEEKEND_DELIVERY_TIMESLOT = '11:00 - 14:00';
    public const WEEKDAY_DELIVERY_TIME_START = 16;
    public const WEEKEND_DELIVERY_TIME_START = 11;
    public const WEEKEND_AVAILABLE_HOURS = 'weekend_available_hours';
    /**
     * @param int|null $storeId
     * @return array
     */
    public function getDeliveryHours(int $storeId = null, $isWeekend = false): array
    {
        $hoursSetting = (string)$this->getValue(self::DELIVERY_DATE_BLOCK . self::AVAILABLE_HOURS, $storeId);
        if ($isWeekend) {
            $hoursSetting = (string)$this->getValue(self::DELIVERY_DATE_BLOCK . self::WEEKEND_AVAILABLE_HOURS, $storeId);
        }

        // @TODO ADD HOLIDAY CONDITION

        $intervals = preg_split('#\s*,\s*#', $hoursSetting, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($hoursSetting) || empty($intervals[0])) {
            $timeStart = $isWeekend ? self::WEEKEND_DELIVERY_TIME_START : self::WEEKDAY_DELIVERY_TIME_START;
            $timeRangeFormatted = $isWeekend ? self::WEEKEND_DELIVERY_TIMESLOT : self::WEEKDAY_DELIVERY_TIMESLOT;
        }   else {
            $timeRange = explode('-', $intervals[0]);
            $timeStart = $timeRange[0];
            $timeRangeFormatted = $timeRange[0].':00 - '.$timeRange[1].':00';
        }

        return [
            [
                'value' => '-1',
                'label' => ' ',
            ],
            [
                'value' => $timeStart,
                'label' => $timeRangeFormatted,
            ]
        ];

    }

    /**
     * @param array $intervals
     * @return array
     */
    private function getHours(array $intervals): array
    {
        $hours = [];
        foreach ($intervals as $interval) {
            if (preg_match('#(?P<lower>\d+)(\s*-\s*(?P<upper>\d+))?#', $interval, $matches)) {
                $lower = (int)$matches['lower'];
                if ($lower > 23) {
                    continue;
                }

                if (isset($matches['upper'])) {
                    $upper = (int)$matches['upper'];
                    if ($upper > 24) {
                        continue;
                    }

                    $upper--;

                    if ($lower > $upper) {
                        continue;
                    }
                } else {
                    $upper = $lower;
                }

                $range = range($lower, $upper);
                $hours = $this->mergeHours($hours, $range);
            }
        }

        return $hours;
    }

    /**
     * @param array $hours
     * @param array $range
     * @return array
     */
    private function mergeHours(array $hours, array $range): array
    {
        return array_merge($hours, $range);
    }


}
