<?php

namespace X247Commerce\StoreLocatorSource\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\Framework\App\ObjectManager;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration\GetData as GetDataModel;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ModifySourceItemConfiguration
{
    private $locator;
    private $scopeConfig;
    private $getDataResourceModel;

	public function __construct(
        LocatorInterface $locator,
        ScopeConfigInterface $scopeConfig,
        GetDataModel $getDataResourceModel = null
	) {
        $this->locator = $locator;
        $this->scopeConfig = $scopeConfig;
        $this->getDataResourceModel = $getDataResourceModel ?: ObjectManager::getInstance()->get(GetDataModel::class);
    }

    public function afterModifyData(
    	\Magento\InventoryLowQuantityNotificationAdminUi\Ui\DataProvider\Product\Form\Modifier\SourceItemConfiguration $subject,
    	$result
    ) {	    	

    	$product = $this->locator->getProduct();

        if(isset($result[$product->getId()]['sources']['unassigned_sources'])) {
	        $unassignedSources = $result[$product->getId()]['sources']['unassigned_sources'];
	        $result[$product->getId()]['sources']['unassigned_sources'] = $this->getSourceItemsConfigurationData(
	            $unassignedSources,
	            $product
	        );
        }
    	
		return $result;
	}


    private function getSourceItemsConfigurationData(array $assignedSources, ProductInterface $product): array
    {
        foreach ($assignedSources as &$source) {
            $sourceItemConfigurationData = $this->getDataResourceModel->execute(
                (string)$source[SourceInterface::SOURCE_CODE],
                $product->getSku()
            );
            $sourceItemConfigurationData = $sourceItemConfigurationData
                ?: [SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY => null];

            $source[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY] =
                $sourceItemConfigurationData[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY];

            $source['notify_stock_qty_use_default'] = '0';
            if ($source[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY] === null) {
                $notifyQtyConfigValue = $this->getNotifyQtyConfigValue();
                $source[SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY] = $notifyQtyConfigValue;
                $source['notify_stock_qty_use_default'] = '1';
            }
        }
        unset($source);

        return $assignedSources;
    }

    private function getNotifyQtyConfigValue(): float
    {
        return (float)$this->scopeConfig->getValue('cataloginventory/item_options/notify_stock_qty');
    }
}