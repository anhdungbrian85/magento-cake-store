<?php

namespace X247Commerce\StoreLocator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\ResourceConnection;

class DeliveryArea extends AbstractHelper
{
	const STORE_LOCATION_DELIVERY_AREA = 'store_location_delivery_area';
    protected $storeManager;
    protected $inlineTranslation;
    protected $logger;
    protected $backendHelper;
    protected $resource;
    protected $connection;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        Data $backendHelper,
        ResourceConnection $resource
    ) 
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->logger = $context->getLogger();
        $this->backendHelper = $backendHelper;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
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
        $inputCode = mb_strtoupper($inputCode);
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
}