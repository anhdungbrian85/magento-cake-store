<?php

namespace X247Commerce\OrderDetails\Block\Onepage;

class Success extends \Magento\Checkout\Block\Onepage\Success {

    protected $orderRepository;
    protected $renderer;
    protected $_productRepository;
    protected $_categoryCollectionFactory;
    protected $orderAmastyFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Amasty\StorePickupWithLocator\Model\ResourceModel\Order\CollectionFactory $orderAmastyFactory,
        \Magento\Sales\Model\Order\Address\Renderer $renderer,
        array $data = []
    ) {
        $this->orderInterface = $orderInterface;
        $this->orderAmastyFactory = $orderAmastyFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->renderer = $renderer;
        parent::__construct(
            $context, $checkoutSession, $orderConfig, $httpContext, $data
        );
    }

    public function getOrder($id) {
        return $this->orderInterface->loadByIncrementId($id);
    }

    public function getDeliveryDateTime($order)
    {   
        return $this->orderAmastyFactory->create()
                    ->addFieldToSelect(['time_from', 'date'])
                    ->addFieldToFilter('order_id', ['eq' => $order->getId()])
                    ->getFirstItem()
                    ->getData();
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
}
