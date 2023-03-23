<?php
namespace X247Commerce\Checkout\Observer\Quote;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use X247Commerce\Catalog\Model\ProductSourceAvailability;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Psr\Log\LoggerInterface;

class QuoteSubmitBefore implements ObserverInterface
{
    protected $checkoutSession;
    protected $logger;
    private $sourceRepository;
    private $searchCriteriaBuilderFactory;
    private $productSourceAvailability;
    protected $locatorSourceResolver;
    protected $storeLocationContext;

    public function __construct(
        CheckoutSession $checkoutSession,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        ProductSourceAvailability $productSourceAvailability,
        LocatorSourceResolver $locatorSourceResolver,
        StoreLocationContextInterface $storeLocationContext,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->productSourceAvailability = $productSourceAvailability;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->storeLocationContext = $storeLocationContext;
        $this->logger = $logger;
    }

    public function execute(EventObserver $observer)
    {
        $locationId = $this->storeLocationContext->getStoreLocationId() ?? $this->checkoutSession->getStoreLocationId();
        // $this->logger->log('600', '$sources '.print_r($locationId, true));
        if ($locationId) {
            
            $order = $observer->getEvent()->getOrder();
            $quote = $observer->getEvent()->getQuote();
            
            foreach ($order->getAllItems() as $item) {
                $available = $this->locatorSourceResolver->checkProductAvailableInStore($locationId, $item);
                if (!$available) {
                    throw new LocalizedException(__('Some of the products are out stock!'));
                }
            }
        } else {
            throw new LocalizedException(__('Please choose a store!'));
        }
        return;
    }
}