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

class ValidateDeliveryPostcode extends Action
{

    protected LocationFactory $locationFactory;

    protected DeliveryArea $deliveryAreaHelper;

    protected JsonFactory $resultJsonFactory;

    protected DeliveryData $deliveryData;

    protected LocatorSourceResolver $locatorSourceResolver;

    protected $checkoutSession;

    public function __construct(
        Context $context,
        LocationFactory $locationFactory,
        DeliveryArea $deliveryAreaHelper,
        JsonFactory $resultJsonFactory,
        DeliveryData $deliveryData,
        LocatorSourceResolver $locatorSourceResolver,
        Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->deliveryData = $deliveryData;
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
                    'message' => __('Choose ‘Collect In Store’ or use another delivery address. Please check the post code you entered includes a space before the last three digits e.g. CV21 9HG')
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
