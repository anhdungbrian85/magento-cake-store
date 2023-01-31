<?php

namespace X247Commerce\Checkout\Observer\Order;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use X247Commerce\Catalog\Model\ProductSourceAvailability;
use Magento\Framework\Exception\PaymentException;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class ValidateBeforeOrder implements ObserverInterface
{
    protected $customerSession;

    protected $logger;

    private $sourceRepository;

    private $searchCriteriaBuilderFactory;

    private $productSourceAvailability;
    protected $locatorSourceResolver;

    public function __construct(
        CustomerSession $customerSession,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        ProductSourceAvailability $productSourceAvailability,
        LocatorSourceResolver $locatorSourceResolver,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->sourceRepository = $sourceRepository;
        $this->productSourceAvailability = $productSourceAvailability;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->locatorSourceResolver = $locatorSourceResolver;
    }

    public function execute(EventObserver $observer)
    {
        $locationId = $this->customerSession->getStoreLocationId();
        if ($locationId) {
            $sources = $this->locatorSourceResolver->getSourceCodeByAmLocator($locationId);
            if (empty($sources)) {
                throw new PaymentException(__("Some of the products are not available in the selected store."));
            }
            $order = $observer->getEvent()->getOrder();
            $proSku = [];
            foreach ($order->getAllItems() as $item) {
                $proSku[] = $item->getSku();
            }
            // $this->logger->log('600', 'Selected Skus '.print_r($proSku, true));
            foreach ($proSku as $sku) {
                $productQty = $this->productSourceAvailability->getQuantityInformationForProduct($sku);
                $sourceList = [];
                foreach ($productQty as $pQty) {
                    if ($pQty['source_code'] == $sources) {
                        $sourceList[] = $pQty;
                    }
                }        

                if ($sourceList) {
                    $inStock = 0;
                    foreach ($sourceList as $qty) {
                        if ($qty['quantity'] == 0 || !$qty['status']) {
                            $inStock += 0;
                        } else {
                            $inStock += 1;
                        }
                    }

                    if ($inStock == 0) {
                        throw new PaymentException(__('Some of the products are out of stock.'));
                    }
                } else {
                    throw new PaymentException(__('Some of the products are not available.'));     
                }
            }
        } else {
            throw new PaymentException(__('Please choose a store!'));
        }
        return;
    }
}
