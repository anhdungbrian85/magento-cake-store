<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use Magento\Customer\Model\Session as CustomerSession;
use WeltPixel\GA4\Api\ServerSide\Events\ViewItemInterface;
use WeltPixel\GA4\Api\ServerSide\Events\ViewItemInterfaceFactory;
use WeltPixel\GA4\Api\ServerSide\Events\ViewItemItemInterfaceFactory;
use WeltPixel\GA4\Helper\ServerSideTracking as GA4Helper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use WeltPixel\GA4\Model\Dimension as DimensionModel;

class ViewItemBuilder implements \WeltPixel\GA4\Api\ServerSide\Events\ViewItemBuilderInterface
{
    /**
     * @var ViewItemInterfaceFactory
     */
    protected $viewItemFactory;

    /**
     * @var ViewItemItemInterfaceFactory
     */
    protected $viewItemItemFactory;

    /**
     * @var GA4Helper
     */
    protected $ga4Helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var DimensionModel
     */
    protected $dimensionModel;

    /**
     * @param ViewItemInterfaceFactory $viewItemFactory
     * @param ViewItemItemInterfaceFactory $viewItemItemFactory
     * @param GA4Helper $ga4Helper
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerSession $customerSession
     * @param DimensionModel $dimensionModel
     */
    public function __construct(
        ViewItemInterfaceFactory $viewItemFactory,
        ViewItemItemInterfaceFactory $viewItemItemFactory,
        GA4Helper $ga4Helper,
        ProductRepositoryInterface $productRepository,
        CustomerSession $customerSession,
        DimensionModel $dimensionModel
    )
    {
        $this->viewItemFactory = $viewItemFactory;
        $this->viewItemItemFactory = $viewItemItemFactory;
        $this->ga4Helper = $ga4Helper;
        $this->productRepository = $productRepository;
        $this->customerSession = $customerSession;
        $this->dimensionModel = $dimensionModel;
    }

    /**
     * @param $productId
     * @return null|ViewItemInterface
     */
    function getViewItemEvent($productId)
    {
        /** @var ViewItemInterface $viewItemEvent */
        $viewItemEvent = $this->viewItemFactory->create();

        if (!$productId) {
            return $viewItemEvent;
        }
        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Exception $ex) {
            return $viewItemEvent;
        }

        $clientId = $this->ga4Helper->getClientId();
        $sessionIdAndTimeStamp = $this->ga4Helper->getSessionIdAndTimeStamp();
        $userId = $this->customerSession->getCustomerId();

        $currencyCode = $this->ga4Helper->getCurrencyCode();
        $productPrice = number_format($product->getPriceInfo()->getPrice('final_price')->getValue(), 2, '.', '');

        if ($this->ga4Helper->sendUserIdInEvents() && $userId) {
            $viewItemEvent->setUserId($userId);
        }
        $viewItemEvent->setClientId($clientId);
        if ($sessionIdAndTimeStamp['session_id']) {
            $viewItemEvent->setSessionId($sessionIdAndTimeStamp['session_id']);
        }
        if ($sessionIdAndTimeStamp['timestamp']) {
            $viewItemEvent->setTimestamp($sessionIdAndTimeStamp['timestamp']);
        }
        $viewItemEvent->setCurrency($currencyCode);
        $viewItemEvent->setValue($productPrice);

        $productItemOptions = [];
        $productItemOptions['currency'] = $currencyCode;
        $productItemOptions['item_name'] = html_entity_decode($product->getName());
        $productItemOptions['item_id'] = $this->ga4Helper->getGtmProductId($product);
        $productItemOptions['affiliation'] = $this->ga4Helper->getAffiliationName();
        $productItemOptions['price'] = $productPrice;
        if ($this->ga4Helper->isBrandEnabled()) {
            $productItemOptions['item_brand'] = $this->ga4Helper->getGtmBrand($product);
        }

        $productCategoryIds = $product->getCategoryIds();
        $ga4Categories = $this->ga4Helper->getGA4CategoriesFromCategoryIds($productCategoryIds);
        $productItemOptions = array_merge($productItemOptions, $ga4Categories);
        $productItemOptions['quantity'] = 1;
        $productItemOptions['index'] = 0;

        /**  Set the custom dimensions */
        $customDimensions = $this->dimensionModel->getProductDimensions($product, $this->ga4Helper);
        foreach ($customDimensions as $name => $value) :
            $productItemOptions[$name] = $value;
        endforeach;

        $viewItemItem = $this->viewItemItemFactory->create();
        $viewItemItem->setParams($productItemOptions);

        $viewItemEvent->addItem($viewItemItem);

        return $viewItemEvent;
    }
}
