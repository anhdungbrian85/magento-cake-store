<?php

namespace X247Commerce\Delivery\Plugin;

use Magento\Quote\Model\Quote\Address\RateCollectorInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Shipping\Model\Shipping;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use X247Commerce\StoreLocator\Helper\DeliveryArea as DeliveryAreaHelper;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class CustomModelShipping
{
    protected $logger;
	protected CollectionFactory $locationCollectionFactory;
	protected StoreLocationContextInterface $storeLocationContextInterface;
	protected DeliveryAreaHelper $deliveryAreaHelper;
	protected $_checkoutSession;
	protected $messageManager;
	protected $_quote;
	protected $request;
	protected $locatorSourceResolver;
    
    public function __construct(
       LoggerInterface $logger,
	   DeliveryAreaHelper $deliveryAreaHelper,
	   \Magento\Checkout\Model\Session $checkoutSession,
	   LocatorSourceResolver $locatorSourceResolver,
	   StoreLocationContextInterface $storeLocationContextInterface,
	   ManagerInterface $messageManager,
	   Quote $quote,
	   Http $request,
	   CollectionFactory $locationCollectionFactory
    ) {
        $this->logger = $logger;
		$this->_quote = $quote;
		$this->request = $request;
		$this->messageManager = $messageManager;
		$this->checkoutSession = $checkoutSession;
		$this->locatorSourceResolver = $locatorSourceResolver;
		$this->deliveryAreaHelper = $deliveryAreaHelper;
		$this->storeLocationContextInterface = $storeLocationContextInterface;
		$this->locationCollectionFactory = $locationCollectionFactory;
    }
    
    public function afterCollectRates(
        \Magento\Shipping\Model\Shipping $subject,
        $collectRatesResult
    ){
		
		
		$quote = $this->checkoutSession->getQuote();
		// Get the shipping address
        $shippingAddress = $quote->getShippingAddress();		
        
        // Get the shipping address postcode
        $postcode = $shippingAddress->getPostcode();		     
		$this->logger->info('POSTCODE '.$postcode);		
        if($quote->getShippingAddress()) {
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();            
            if($shippingMethod == 'cakeboxdelivery_cakeboxdelivery'){
				if($postcode && $postcode != '-'){
					$location = $this->getClosestStoreLocation($postcode);
					if ($location && $location->getId()) {
						$this->logger->info('LOCATION NAME '.$location->getName());
						$this->logger->info('LOCATION DELIVERY '.$location->getEnableDelivery());
						
						if (!empty($quote->getAllVisibleItems())) {
							foreach ($quote->getAllVisibleItems() as $quoteItem) {
								$productSkus[] = $quoteItem->getSku();
							}
						}
						$closestLocation = $this->locatorSourceResolver->getClosestLocationHasProducts($location->getId(), $productSkus);
						$this->logger->info('LOCATION SKUCHECK '.json_encode($closestLocation));
						if (!empty($closestLocation)) {
							$this->storeLocationContextInterface->setStoreLocationId($location->getId());														
							return $collectRatesResult;	
						}else{
							$this->messageManager->addErrorMessage(__('There are no sources in the cart that match the items in the cart!'));							
							
							return $collectRatesResult;							
						}						
					}else{
						$this->messageManager->addErrorMessage(__('There are no sources in the cart that match the items in the cart!'));						
						
						return $collectRatesResult;
					}	
				}else{
					return $collectRatesResult;
				}
			}else{
				return $collectRatesResult;
			}				
        }else{
			return $collectRatesResult;
		}			
    }
	
	public function getClosestStoreLocation($postcode)
    {   
        if (!$postcode) {
            return false;
        }
        $needToPrepareCollection = false;
        $location = $this->locationCollectionFactory->create()->addFieldToFilter('enable_delivery', ['eq' => 1]);
        $deliverLocations = $this->deliveryAreaHelper->getDeliverLocations($postcode);
        $deliverLocationsIds = [];

        foreach ($deliverLocations as $deliverLocation) {
            $deliverLocationsIds[] = $deliverLocation->getStoreId();
        }
        $location->addFieldtoFilter('id', ['in' => $deliverLocationsIds]);
        $location->applyDefaultFilters();
        return $location->getFirstItem();
    }
}
