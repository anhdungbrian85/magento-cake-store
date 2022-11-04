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

class ValidateBeforeOrder implements ObserverInterface
{
    protected $customerSession;

    protected $logger;

    private $sourceRepository;

    private $searchCriteriaBuilderFactory;

    private $productSourceAvailability;

    public function __construct(
        CustomerSession $customerSession,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        ProductSourceAvailability $productSourceAvailability,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->sourceRepository = $sourceRepository;
        $this->productSourceAvailability = $productSourceAvailability;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    public function execute(EventObserver $observer)
    {
        $locationId = $this->customerSession->getStoreLocationId();
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->addFilter('amlocator_store', $locationId, 'in')->create();
        $sources = $this->sourceRepository->getList($searchCriteria)->getItems();
        if ($sources) {
            $sourceCodes = [];
            foreach ($sources as $source) {
                $sourceCodes[] =  $source->getSourceCode();
            }
        } else {
            throw new PaymentException(__("Store's source is not available."));
        }
        $order = $observer->getEvent()->getOrder();
        $proSku = [];
        foreach ($order->getAllItems() as $item) {
            $proSku[] = $item->getSku();
        }
        foreach ($proSku as $sku) {
            $productQty = $this->productSourceAvailability->getQuantityInformationForProduct($sku);
            $sourceList = [];
            foreach ($productQty as $pQty) {
                if (in_array($pQty['source_code'], $sourceCodes)) {
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
        return;
    }
}
