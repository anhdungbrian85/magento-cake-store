<?php

namespace X247Commerce\OrderDetails\Block\Onepage;

use Amasty\Storelocator\Model\LocationFactory;

class Success extends \Magento\Checkout\Block\Onepage\Success {

    protected $orderRepository;
    protected $renderer;
    protected $_productRepository;
    protected $_categoryCollectionFactory;
    protected $orderAmastyFactory;
    protected $orderInterface;
    protected $configTimeDelivery;
    protected $deliveryAmastyFactory;
    protected LocationFactory $locationFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Amasty\StorePickupWithLocator\Model\ResourceModel\Order\CollectionFactory $orderAmastyFactory,
        \Amasty\CheckoutDeliveryDate\Model\ResourceModel\Delivery\CollectionFactory $deliveryAmastyFactory,
        \Amasty\CheckoutDeliveryDate\Model\ConfigProvider $configTimeDelivery,
        \Magento\Sales\Model\Order\Address\Renderer $renderer,
        LocationFactory $locationFactory,
        array $data = []
    ) {
        $this->orderInterface = $orderInterface;
        $this->orderAmastyFactory = $orderAmastyFactory;
        $this->configTimeDelivery = $configTimeDelivery;
        $this->deliveryAmastyFactory = $deliveryAmastyFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->renderer = $renderer;
        $this->locationFactory = $locationFactory;
        parent::__construct(
            $context, $checkoutSession, $orderConfig, $httpContext, $data
        );
    }

    public function getOrder()
    {
        return $this->orderInterface->loadByIncrementId($this->getOrderId());
    }

    public function getDeliveryDateTime($order)
    {
        if ($order->getShippingMethod() == 'amstorepickup_amstorepickup') {
            return $this->orderAmastyFactory->create()
                        ->addFieldToSelect(['time_from', 'date'])
                        ->addFieldToFilter('order_id', ['eq' => $order->getId()])
                        ->getFirstItem()
                        ->getData();
        } else {
            return $this->deliveryAmastyFactory->create()
                        ->addFieldToSelect(['time', 'date'])
                        ->addFieldToFilter('order_id', ['eq' => $order->getId()])
                        ->getFirstItem()
                        ->getData();
        }
    }

    public function getFormatTime($time)
    {
        return gmdate("g:i A", $time);
    }

    public function getFormatDate($date)
    {
        return date('d/m/Y', strtotime($date));
    }

    public function getFormatedAddress($address) {
        return $this->renderer->format($address, 'html');
    }

    public function getDeliveryHours($deliveryDayTime)
    {
        if (array_key_exists('time', $deliveryDayTime)) {
            $key = $deliveryDayTime['time'];
            $isWeekendTimeSlot = ($key == \X247Commerce\Checkout\Model\Config\DeliveryConfigProvider::WEEKEND_DELIVERY_TIME_START);
            $arrayHoursValue = $this->configTimeDelivery->getDeliveryHours(null, $isWeekendTimeSlot);

            return $arrayHoursValue[array_search($key, array_column($arrayHoursValue, 'value'))]['label'];
        }

        return '--';
    }

    public function getPaymentMethodtitle($order) {
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        return $method->getTitle();
    }

    public function getCategoryName($id)
    {
        $product = $this->_productRepository->getById($id);
        $categoryIds = $product->getCategoryIds();
        $categories = $this->getCategoryCollection()
                            ->addAttributeToFilter('entity_id', $categoryIds);

        foreach ($categories as $category) {
            return $category->getName();
        }
    }

    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    public function getStoreLocation()
    {
        $locationId = $this->getOrder()->getStoreLocationId();
        return $locationId ? $this->locationFactory->create()->load($locationId) : null;
    }

    public function getStoreLocationUrl()
    {
        $storeLocation = $this->getStoreLocation();
        if ($storeLocation) {
            return $this->getBaseUrl(). 'storelocator/'. $storeLocation->getUrlKey();
        }
        return false;
    }
}
