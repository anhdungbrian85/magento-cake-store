<?php

namespace X247Commerce\Checkout\Observer\Cart;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use X247Commerce\Catalog\Model\ProductSourceAvailability;

class ValidateAddToCart implements ObserverInterface
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
        try {

            $locationId = $this->customerSession->getStoreLocationId();

            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
            $searchCriteria = $searchCriteriaBuilder->addFilter('amlocator_store', $locationId, 'in')->create();
            $sources = $this->sourceRepository->getList($searchCriteria)->getItems();
            $sourceCodes = [];
            foreach ($sources as $source) {
                $sourceCodes[] =  $source->getSourceCode();
            }
            
            $product = $observer->getProduct();
            $productQty = $this->productSourceAvailability->getQuantityInformationForProduct($product->getSku());
            foreach ($productQty as $qty) {
                if (in_array($qty['source_code'], $sourceCodes)) {
                    if ($qty['quantity'] == 0 || !$qty['status']) {
                        $observer->getRequest()->setParam('product', false);
                        $observer->getRequest()->setParam('return_url', false);
                        $this->_messageManager->addErrorMessage(__('The product is not available in selected store!'));
                    }
                } else {
                    $observer->getRequest()->setParam('product', false);
                    $observer->getRequest()->setParam('return_url', false);
                    $this->_messageManager->addErrorMessage(__('The product is not available in current selected store!'));
                }
            }
            
        } catch (\Exception $e) {
            $this->logger->addLog('Something wrong while add to cart: ' . $e->getMessage());
        }
        return;
    }
}
