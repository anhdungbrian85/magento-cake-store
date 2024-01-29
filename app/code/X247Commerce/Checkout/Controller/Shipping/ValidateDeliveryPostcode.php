<?php

namespace X247Commerce\Checkout\Controller\Shipping;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Amasty\Storelocator\Model\LocationFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use X247Commerce\Delivery\Helper\DeliveryData;
use X247Commerce\StoreLocator\Helper\DeliveryArea;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ValidateDeliveryPostcode extends Action
{

    protected LocationFactory $locationFactory;

    protected DeliveryArea $deliveryAreaHelper;

    protected JsonFactory $resultJsonFactory;

    protected DeliveryData $deliveryData;

    protected LocatorSourceResolver $locatorSourceResolver;

    protected $checkoutSession;

    protected $logger;

    protected $productRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        Context $context,
        LocationFactory $locationFactory,
        DeliveryArea $deliveryAreaHelper,
        JsonFactory $resultJsonFactory,
        DeliveryData $deliveryData,
        LocatorSourceResolver $locatorSourceResolver,
        Session $checkoutSession,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->deliveryData = $deliveryData;
        $this->locationFactory = $locationFactory;
        $this->deliveryAreaHelper = $deliveryAreaHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $postcode = $this->getRequest()->getParam('postcode');
        if (!$postcode) {
            return $resultJson->setData(
                ['status' => -1]
            );
        }
        $wlAreaCollection = $this->deliveryAreaHelper->getDeliverLocations($postcode);
        if (!$wlAreaCollection->count()) {
            return $resultJson->setData(
                [
                    'status' => false,
                    'message' => __('Choose ‘Collect In Store’ or use another delivery address. Please check the post code you entered includes a space before the last three digits e.g. CV21 9HG')
                ]
            );
        } else {
            $locationDataFromPostCode = $this->deliveryData->getLongAndLatFromPostCode($postcode);

            if ($locationDataFromPostCode['status']) {
                $productSkus = [];
                $productIds = [];
                $quote = $this->checkoutSession->getQuote();
                if (!empty($quote->getAllVisibleItems())) {
                    foreach ($quote->getAllVisibleItems() as $quoteItem) {
                        if($quoteItem->getProductType() == 'bundle'){
                            $itemOptions = $quoteItem->getdata('qty_options');
                            foreach ($itemOptions as $key => $value) {
                                $productIds[] = $key;
                            }
                            $newChildData = $this->loadProductsByIds($productIds);
                            foreach($newChildData as $childData){
                                $productSkus[] = $childData->getSku();
                            }
                        }else{
                            $productSkus[] = $quoteItem->getSku();
                        }
                        $this->logger->info('Item Data :'. json_encode($quoteItem->getData(), true));
                        $this->logger->info('Item Data :'. print_r($quoteItem->getProductType(), true));
                    }
                    $this->logger->info('children Data :'. print_r($productIds, true));
                    $this->logger->info('Product SKU :'. print_r($productSkus, true));
                }
                $location = $this->locatorSourceResolver->getClosestStoreLocationWithPostCodeAndSkus(
                    $postcode,
                    $locationDataFromPostCode['data']['lat'],
                    $locationDataFromPostCode['data']['lng'],
                    $productSkus
                );
                if (!$location->getId()) {
                    return $resultJson->setData(
                        [
                            'status' => false,
                            'message' => __('We are unable to deliver those products to your location, please arrange to collect in store!')
                        ]
                    );
                }
            }
        }

        return $resultJson->setData(
            [
                'status' => true
            ]
        );
    }

    public function loadProductsByIds(array $productIds)
    {
        // Build search criteria
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $productIds, 'in')
            ->create();

        // Load products
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        // $products now contains the loaded products

        return $products;
    }
}
