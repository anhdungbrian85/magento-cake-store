<?php

namespace X247Commerce\Checkout\Observer\Cart;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use X247Commerce\Catalog\Model\ProductSourceAvailability;
<<<<<<< HEAD
=======
use Magento\Framework\Exception\LocalizedException;
>>>>>>> d8a4661090a58c7e0e7ad364f1690fadc87e4a03

class ValidateAddToCart implements ObserverInterface
{
    protected $customerSession;

<<<<<<< HEAD
    protected $logger;

=======
>>>>>>> d8a4661090a58c7e0e7ad364f1690fadc87e4a03
    private $sourceRepository;

    private $searchCriteriaBuilderFactory;

    private $productSourceAvailability;

<<<<<<< HEAD
=======
    protected $_messageManager;

>>>>>>> d8a4661090a58c7e0e7ad364f1690fadc87e4a03
    public function __construct(
        CustomerSession $customerSession,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
<<<<<<< HEAD
        ProductSourceAvailability $productSourceAvailability,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->sourceRepository = $sourceRepository;
        $this->productSourceAvailability = $productSourceAvailability;
=======
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ProductSourceAvailability $productSourceAvailability
    ) {
        $this->customerSession = $customerSession;
        $this->sourceRepository = $sourceRepository;
        $this->productSourceAvailability = $productSourceAvailability;
        $this->_messageManager = $messageManager;
>>>>>>> d8a4661090a58c7e0e7ad364f1690fadc87e4a03
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    public function execute(EventObserver $observer)
    {
<<<<<<< HEAD
        try {

            $locationId = $this->customerSession->getStoreLocationId();
=======
        $locationId = $this->customerSession->getStoreLocationId();

        if ($locationId) {
>>>>>>> d8a4661090a58c7e0e7ad364f1690fadc87e4a03

            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
            $searchCriteria = $searchCriteriaBuilder->addFilter('amlocator_store', $locationId, 'in')->create();
            $sources = $this->sourceRepository->getList($searchCriteria)->getItems();
<<<<<<< HEAD
            $sourceCodes = [];
            foreach ($sources as $source) {
                $sourceCodes[] =  $source->getSourceCode();
=======
            
            if ($sources) {
                $sourceCodes = [];
                foreach ($sources as $source) {
                    $sourceCodes[] =  $source->getSourceCode();
                }
            } else {
                throw new LocalizedException(__("The product is not available in the selected store."));
>>>>>>> d8a4661090a58c7e0e7ad364f1690fadc87e4a03
            }
            
            $product = $observer->getProduct();
            $productQty = $this->productSourceAvailability->getQuantityInformationForProduct($product->getSku());
<<<<<<< HEAD
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
=======
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

>>>>>>> d8a4661090a58c7e0e7ad364f1690fadc87e4a03
        return;
    }
}
