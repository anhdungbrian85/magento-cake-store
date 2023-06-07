<?php

namespace X247Commerce\StoreLocator\Controller\Product;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Action\Context;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class SuggestClosestLocation extends \Magento\Framework\App\Action\Action
{

    protected $storeLocationContext;

    protected $quoteRepository;

    protected $checkoutSession;

    protected $locatorSourceResolver;

	protected $formKeyValidator;

    public function __construct(
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $storeLocationContext,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        LocatorSourceResolver $locatorSourceResolver,
		FormKeyValidator $formKeyValidator,
        Context $context
    ) {
        parent::__construct($context);
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
		$this->formKeyValidator = $formKeyValidator;
        $this->storeLocationContext = $storeLocationContext;
    }

    public function execute()
    {
        try {
			//$formKey = $this->getRequest()->getParam('form_key');
			$params = $this->getRequest()->getParams();
			$quote = $this->checkoutSession->getQuote();
			$productSkus = [$params['currentProductSku']];

			if (!empty($quote->getAllVisibleItems())) {
				foreach ($quote->getAllVisibleItems() as $quoteItem) {
					$productSkus[] = $quoteItem->getSku();
				}
			}
			if ($this->storeLocationContext->getDeliveryType() == 1) {
				$result = [
					'status' => 400,
					'message' => __('Hide block for delivery')
				];
			} else {
                if (!$this->storeLocationContext->getStoreLocationId()) {
                    $result = [
                        'status' => 400,
                        'message' => __('Hide block for delivery')
                    ];
                } else {
                    $closestLocation = $this->locatorSourceResolver->getClosestLocationsHasProducts($this->storeLocationContext->getStoreLocationId(), $productSkus);
                    if (!empty($closestLocation['location_data'])) {
                        $result = [
                            'status' => 200,
                            'message' => __('Okay!'),
                            'closest_location' => $closestLocation['location_data']
                        ];
                    } else {
                        if ($closestLocation['current_source_is_available']) {
                            $result = [
                                'status' => 400,
                                'message' => __('This product is in this stock')
                            ];
                        } else {
                            $result = [
                                'status' => 404,
                                'message' => __('There are no sources in the cart that match the items in the cart!')
                            ];
                        }
                    }
                }
			}

        } catch (\Exception $e) {
            $result = [
                'status' => 500,
                'message' => __('There are no sources in the cart that match the items in the cart!'),
                'debug_note' => $e->getMessage()
            ];
        }


        return $this->getResponse()->setBody(json_encode($result));
    }
}
