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
        $select = $this->connection->select()->from($tableName, ['*'])->__toString();
        $data = $this->connection->fetchAll($select);
        // var_dump($data);
        return $data;
    }

    public function checkInputPostcode($inputCode)
    {
        $listDeliveryArea = $this->getListDeliveryArea();

        foreach ($listDeliveryArea as $deliArea) {
            if ($deliArea['status'] == 1 && !empty($deliArea['postcode'])) {
                if ($inputCode === $deliArea['postcode']) {
                    
                    return true;
                } else {
                    $patternCode = '/'.$deliArea['postcode'].' /';
                    
                    if (preg_match($patternCode, $inputCode)){
                        if ($deliArea['matching_strategy'] == 'Match Prefix') {
                            
                            return true;
                        } else {
                            if ($inputCode == $deliArea['postcode']) {
                                
                                return true;
                            }
                        }                
                    }
                }
            }
        }
        return false;
    }
}