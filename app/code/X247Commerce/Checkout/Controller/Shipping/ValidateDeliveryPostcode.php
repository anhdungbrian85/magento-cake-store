<?php

namespace X247Commerce\Checkout\Controller\Shipping;

use Magento\Checkout\Controller\Checkout;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection as LocationCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use X247Commerce\Delivery\Helper\DeliveryData;
use X247Commerce\StoreLocator\Helper\DeliveryArea;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class ValidateDeliveryPostcode extends Action
{

    protected StoreLocationContextInterface $storeLocationContext;

    protected LocationFactory $locationFactory;

    protected DeliveryArea $deliveryAreaHelper;

    protected JsonFactory $resultJsonFactory;

    protected DeliveryData $deliveryData;

    protected LocatorSourceResolver $locatorSourceResolver;

    protected $checkoutSession;

    public function __construct(
        Context $context,
        StoreLocationContextInterface $storeLocationContext,
        LocationFactory $locationFactory,
        DeliveryArea $deliveryAreaHelper,
        JsonFactory $resultJsonFactory,
        DeliveryData $deliveryData,
        LocatorSourceResolver $locatorSourceResolver,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->deliveryData = $deliveryData;
        $this->storeLocationContext = $storeLocationContext;
        $this->locationFactory = $locationFactory;
        $this->deliveryAreaHelper = $deliveryAreaHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->checkoutSession = $checkoutSession;
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
                    'message' => __('We do not yet deliver to that area. Please arrange to collect in-store or use another delivery address!')
                ]
            );
        } else {
            $locationDataFromPostCode = $this->deliveryData->getLongAndLatFromPostCode($postcode);

            if ($locationDataFromPostCode['status']) {
                $productSkus = [];
                $quote = $this->checkoutSession->getQuote();
                if (!empty($quote->getAllVisibleItems())) {
                    foreach ($quote->getAllVisibleItems() as $quoteItem) {
                        $productSkus[] = $quoteItem->getSku();
                    }
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
}
