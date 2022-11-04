<?php

namespace X247Commerce\Checkout\Observer\Cart;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use X247Commerce\Catalog\Model\ProductSourceAvailability;
use Magento\Framework\Exception\LocalizedException;

class ValidateAddToCart implements ObserverInterface
{
    protected $customerSession;

    private $sourceRepository;

    private $searchCriteriaBuilderFactory;

    private $productSourceAvailability;

    protected $_messageManager;

    public function __construct(
        CustomerSession $customerSession,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ProductSourceAvailability $productSourceAvailability
    ) {
        $this->customerSession = $customerSession;
        $this->sourceRepository = $sourceRepository;
        $this->productSourceAvailability = $productSourceAvailability;
        $this->_messageManager = $messageManager;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    public function execute(EventObserver $observer)
    {
        $locationId = $this->customerSession->getStoreLocationId();

        if ($locationId) {

            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
            $searchCriteria = $searchCriteriaBuilder->addFilter('amlocator_store', $locationId, 'in')->create();
            $sources = $this->sourceRepository->getList($searchCriteria)->getItems();
            
            if ($sources) {
                $sourceCodes = [];
                foreach ($sources as $source) {
                    $sourceCodes[] =  $source->getSourceCode();
                }
            } else {
                throw new LocalizedException(__("The product is not available in the selected store."));
            }
            
            $product = $observer->getProduct();
            $productQty = $this->productSourceAvailability->getQuantityInformationForProduct($product->getSku());
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
                    throw new LocalizedException(__('The requested qty is not available in selected store.'));
                }
            } else {
                throw new LocalizedException(__('The product is not available in the selected store.'));        
            }
        }

        return;
    }
}
