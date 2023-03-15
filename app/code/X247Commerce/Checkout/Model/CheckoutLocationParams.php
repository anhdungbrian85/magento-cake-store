<?php

namespace X247Commerce\Checkout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\StorePickupWithLocator\Model\QuoteFactory as PickupQuoteFactory;
use Magento\Quote\Model\Quote\AddressFactory;
use Amasty\StorePickupWithLocator\Api\QuoteRepositoryInterface as PickupQuoteRepositoryInterface;

class CheckoutLocationParams
{
	protected $checkoutSession;
    protected $storeLocationContext;
    protected $logger;
    protected $locationFactory;
    protected $addressFactory;
    protected $pickupQuoteFactory;
    protected $pickupQuoteRepository;

    public function __construct(
        CheckoutSession $checkoutSession,
        StoreLocationContextInterface $storeLocationContext,
        LocationFactory $locationFactory,
        AddressFactory $addressFactory,
        PickupQuoteFactory $pickupQuoteFactory,
        PickupQuoteRepositoryInterface $pickupQuoteRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->storeLocationContext = $storeLocationContext;
        $this->locationFactory = $locationFactory;
        $this->addressFactory = $addressFactory;
        $this->pickupQuoteFactory = $pickupQuoteFactory;
        $this->pickupQuoteRepository = $pickupQuoteRepository;
        $this->logger = $logger;

    }
    public function getConfig()
   	{
   		$quote = $this->checkoutSession->getQuote();
   		$pickupQuote = $this->pickupQuoteRepository->getByAddressId($quote->getShippingAddress()->getId());
       	return [
       		'storeLocationId' => $this->storeLocationContext->getStoreLocationId(), 
       		'deliveryType' => $this->storeLocationContext->getDeliveryType(),
       		'amastySelectedPickup' => [	
       			'am_pickup_curbside' => [],
       			'am_pickup_date' => $pickupQuote->getDate(),
       			'am_pickup_store' => $this->storeLocationContext->getStoreLocationId(),
       			'am_pickup_time' => $pickupQuote->getTimeFrom().'|'.$pickupQuote->getTimeTo()
       		]
       	];
   	}
}
