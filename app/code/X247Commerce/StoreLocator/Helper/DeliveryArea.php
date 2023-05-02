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
        $postcode = mb_strtolower($postcode);
        $isFullyPostcode = strpos(trim($postcode), ' ') !== false;
        $prefix = $isFullyPostcode ? explode(' ', $postcode)[0] : $postcode;
        $wlDeliveryArea = $this->deliveryAreaCollection->create();

        if ($isFullyPostcode) {
            $blDeliveryAreasExact = $this->deliveryAreaCollection->create();
            // get exact blacklist
            $blDeliveryAreasExact->getSelect()
                                    ->where("status = ?", 0)
                                    ->where("matching_strategy = ?", 'Match Exact')
                                    ->where("lower(postcode) = ?", $postcode);

            $blDeliveryAreasExact = $blDeliveryAreasExact->getColumnValues('store_id');
           
            
            $wlDeliveryArea->getSelect()
                            ->where("status = 1 AND matching_strategy = 'Match Exact' AND lower(postcode) = '$postcode'")
                            ->orWhere("status = 1 AND matching_strategy = 'Match Prefix' AND lower(postcode) = '$prefix'");

            if (!empty($blDeliveryAreasExact)) {
                $wlDeliveryArea->getSelect()->where(
                    'store_id not in (?)', implode(',', $blDeliveryAreasExact)
                );
            }
        }   else {
            $blDeliveryAreasPrefix = $this->deliveryAreaCollection->create();
            // get prefix blacklist
            $blDeliveryAreasPrefix->getSelect()
                                    ->where("status = ?", 0)
                                    ->where("matching_strategy = ?", 'Match Prefix')
                                    ->where("lower(postcode) = ?", $prefix);

            $blDeliveryAreasPrefix = $blDeliveryAreasPrefix->getColumnValues('store_id');

            $blStoreIdsPrefix = implode(',', $blDeliveryAreasPrefix);

            $wlDeliveryArea->getSelect()
                                ->where("(status = 1 AND matching_strategy = 'Match Exact' AND lower(postcode) = '$postcode')");

            if (empty($blDeliveryAreasPrefix)) {
                $wlDeliveryArea->getSelect()
                                    ->orWhere("(status = 1 AND matching_strategy = 'Match Prefix' AND lower(postcode) = '$prefix')");
            }   else {
                $wlDeliveryArea->getSelect()
                                    ->orWhere("(status = 1 AND matching_strategy = 'Match Prefix' AND lower(postcode) = '$prefix') AND store_id not in ($blStoreIdsPrefix)");
            }
                    
        }

        return $wlDeliveryArea;
    }

    public function checkPostcodeWithStore($postcode, $storeId) 
    {
        $postcode = mb_strtolower($postcode);
        $isFullyPostcode = strpos(trim($postcode), ' ') !== false;
        $prefix = $isFullyPostcode ? explode(' ', $postcode)[0] : $postcode;
        
        $blDeliveryAreasExact = $this->deliveryAreaCollection->create();
        $blDeliveryAreasExact->getSelect()
                                    ->where("status = ?", 0)
                                    ->where("matching_strategy = ?", 'Match Exact')
                                    ->where("lower(postcode) = ?", $postcode)
                                    ->where('store_id = ?', $storeId);
        if ($blDeliveryAreasExact->count()) {
            // store blacklist exact
            return false;
        }

        $wlDeliveryAreaExact = $this->deliveryAreaCollection->create();
        $wlDeliveryAreaExact->getSelect()
                            ->where("(status = 1 AND matching_strategy = 'Match Exact' AND lower(postcode) = '$postcode' )")
                            ->where('store_id = ?', $storeId);
        if ($wlDeliveryAreaExact->count()) {
            // store whitelist exact
            return true;
        }

        $blDeliveryAreasPrefix = $this->deliveryAreaCollection->create();
        $blDeliveryAreasPrefix->getSelect()
                                    ->where("store_id = ?", $storeId)
                                    ->where("status = ?", 0)
                                    ->where("matching_strategy = ?", 'Match Prefix')
                                    ->where("lower(postcode) = ?", $prefix);

        if ($blDeliveryAreasPrefix->count()) {
            // store blacklist prefix
            return false;
        }    

        $wlDeliveryAreaPrefix = $this->deliveryAreaCollection->create();
        $wlDeliveryAreaPrefix->getSelect()
                                    ->where("status = 1 AND matching_strategy = 'Match Prefix' AND lower(postcode) = '$prefix'")
                                    ->where("store_id = ?", $storeId);

        if ($wlDeliveryAreaPrefix->count()) {
            // store whitelist prefix
            return true;
        } 
        
        return false;
    }
}