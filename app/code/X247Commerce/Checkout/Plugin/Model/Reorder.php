<?php
namespace X247Commerce\Checkout\Plugin\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Reorder\Data\ReorderOutput;
use Magento\Quote\Api\CartRepositoryInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class Reorder
{
    private OrderFactory $orderFactory;
    private ResourceConnection $resource;
    private $connection;
    private StoreLocationContextInterface $storeLocationContext;

    public function __construct(
        OrderFactory $orderFactory,
        ResourceConnection $resource,
        StoreLocationContextInterface $storeLocationContext,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->orderFactory = $orderFactory;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->storeLocationContext = $storeLocationContext;
    }

    /**
     * @throws LocalizedException
     */
    public function afterExecute(
        \Magento\Sales\Model\Reorder\Reorder $subject,
        $result,
        string $orderId,
        string $storeId
    ) {
        if ($result instanceof ReorderOutput) {
            $order = $this->orderFactory->create()->loadByIncrementIdAndStoreId($orderId, $storeId);
            $storeLocationId = $order->getData('store_location_id');
            if (!$storeLocationId) {
                // in the case cannot find store location id 
                $storeLocationId = $this->connection->fetchOne(
                    $this->connection->select()
                        ->from($this->resource->getTableName('amasty_amlocator_location'), 'id')
                        ->where('schedule IS NOT NULL' )
                        ->limit(1)
                );
            }
            $shippingMethod = $order->getData('shipping_method');
            $deliveryType = $shippingMethod === 'amstorepickup_amstorepickup' ? 0 : 1;

            $this->storeLocationContext->setDeliveryType($deliveryType);
            $this->storeLocationContext->setStoreLocationId($storeLocationId);
        }
        return $result;
    }
}
