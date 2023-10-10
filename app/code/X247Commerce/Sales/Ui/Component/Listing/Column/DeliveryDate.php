<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace X247Commerce\Sales\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Address
 */
class DeliveryDate extends Column
{
    /**
     * @var Escaper
     */
    
    protected $timezone;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        array $components = [],
        array $data = []
    ) {
        $this->timezone = $timezone;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $columnName = $this->getData('name');
            foreach ($dataSource['data']['items'] as $key => $item) {
                $dataSource['data']['items'][$key][$columnName] = $this->getDeliveryDateTime($item);
            }
        }
        return $dataSource;
    }

    protected function getDeliveryDateTime($item){
        $deliveryDateTime = '';
        if($item['delivery_date']){
            $delivery_time = $item['delivery_time'] ?? '00';
            $deliveryDateTime = date_create($item['delivery_date'])->format("Y-m-d $delivery_time:i");
        }else if($item['pickup_date'] && ($item['pickup_time_from'] || $item['pickup_time_to'])){
            $deliveryDateTime = $this->getPickupDate($item);
        }
        return $deliveryDateTime;
    }

    protected function getPickupDate($item){
        $timeFrom =  date('H:i', $item['pickup_time_from']);
        $timeTo =  date('H:i', $item['pickup_time_to']);
        $date = date_create($item['pickup_date'])->format('Y-m-d');
        $pickUpDate = $date.' ('.$timeFrom.' - '.$timeTo.')';
        return $pickUpDate;
    }
}
