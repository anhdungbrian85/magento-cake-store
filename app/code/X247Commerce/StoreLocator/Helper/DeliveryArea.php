<?php

namespace X247Commerce\StoreLocator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use X247Commerce\StoreLocator\Model\ResourceModel\DeliveryArea\CollectionFactory as DeliveryAreaCollection; 

class DeliveryArea extends AbstractHelper
{
	const STORE_LOCATION_DELIVERY_AREA = 'store_location_delivery_area';
    protected $storeManager;
    protected $inlineTranslation;
    protected $logger;
    protected $backendHelper;
    protected $resource;
    protected $connection;
    protected $deliveryAreaCollection;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        Data $backendHelper,
        ResourceConnection $resource,
        DeliveryAreaCollection $deliveryAreaCollection
    ) 
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->logger = $context->getLogger();
        $this->backendHelper = $backendHelper;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->deliveryAreaCollection = $deliveryAreaCollection;
    }

    public function getListDeliveryArea()
    {
    	$tableName = $this->resource->getTableName(self::STORE_LOCATION_DELIVERY_AREA);
        $select = $this->connection->select()->from($tableName, ['*'])->order('matching_strategy asc')->__toString();
        $data = $this->connection->fetchAll($select);
        // var_dump($data);
        return $data;
    }

    public function checkInputPostcode($inputCode)
    {
        $listDeliveryArea = $this->getListDeliveryArea();
        $areaMatchExac = [];
        $areaMatchPrefix = [];
        if ($inputCode) {
            $inputCode = mb_strtoupper($inputCode);
        } else {
            $inputCode = '';
        }
        foreach ($listDeliveryArea as $deliArea) {
            if (!empty($deliArea['postcode'])) {
                $checkCode = mb_strtoupper($deliArea['postcode']);
                $patternCode = '/'.$checkCode.' /';
                if ($deliArea['matching_strategy'] == 'Match Exact') {                    
                    if ($inputCode === $checkCode) {
                        
                        if ($deliArea['status'] == 1) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                    $areaMatchExac[] = $deliArea;
                } else {
                    if (preg_match($patternCode, $inputCode)) {
                        
                        if ($deliArea['status'] == 1) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                    $areaMatchPrefix[] = $deliArea;
                }
            }
        }
        return false;
    }

    public function getDeliverLocations($postcode)
    {
        $isFullyPostcode = strpos(trim($postcode), ' ') !== false;
        $prefix = $isFullyPostcode ? explode(' ', $postcode)[0] : $postcode;

        $blDeliveryArea = $this->deliveryAreaCollection->create();
        $blDeliveryArea->getSelect()
            ->where("
                ((status = 0 AND matching_strategy = 'Match Exact' AND postcode = '$postcode')
                OR (status = 0 AND matching_strategy = 'Match Prefix' AND postcode = '$prefix'))
            ");
        $blackListAreas = $blDeliveryArea->getAllIds();

        $wlDeliveryArea = $this->deliveryAreaCollection->create();

        if (!empty($blackListAreas)) {
            $wlDeliveryArea->addFieldtoFilter(
                'id', ['nin' => $blackListAreas] // Ignore black list
            );
        }
        // Add whitelist filter
        $wlDeliveryArea->getSelect()->where("
            ((status = 1 AND matching_strategy = 'Match Exact' AND postcode = '$postcode')
            OR (status = 1 AND matching_strategy = 'Match Prefix' AND postcode = '$prefix'))
        ");
        return $wlDeliveryArea;
    }
}