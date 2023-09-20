<?php

namespace X247Commerce\Checkout\Plugin\Model;

use Magento\Framework\App\ResourceConnection;

class LocationProvider
{
	private ResourceConnection $resource;
	private $connection;

	public function __construct(
		ResourceConnection $resource
	) {
		$this->resource = $resource;
		$this->connection = $resource->getConnection();

	}

    public function afterGetLocationCollection(\Amasty\StorePickupWithLocator\Model\LocationProvider $subject, $result)
    {

        $connection = $this->connection;
        $tableName = $connection->getTableName('store_location_asda_link');
        $query = $connection->select()->from($tableName, ['asda_location_id']);
        $asdaStores = $connection->fetchCol($query);


        $rawData = $subject->getPreparedCollection();
        $deliveryData = [];

        foreach ($rawData->getItems() as $location) {
        	$deliveryData[(int) $location->getId()] = (int) $location->getData('enable_delivery');
        }

        foreach ($result as &$storeData) {
        	$storeData['enable_delivery'] = $deliveryData[$storeData['id']];
        	$storeData['is_asda'] = in_array($storeData['id'], $asdaStores);
            $storeData['current_timezone_offset'] = $this->getStoreTimeZoneOffset($storeData['current_timezone_time']);
        }

        return $result;
    }

    /**
     * @param $storeTimestamp
     * @return float|int
     */
    private function getStoreTimeZoneOffset($storeTimestamp)
    {
        $storeTime = new \DateTime();
        $storeTime->setTimestamp($storeTimestamp);
        $storeTimeHour = $storeTime->format('H');
        $gmtTime = new \DateTime();
        $gmtTime->format('H');
        $gmtTimeHour = $gmtTime->format('H');
        return ($gmtTimeHour - $storeTimeHour) * 60;
    }
}
