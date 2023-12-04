<?php
namespace X247Commerce\SpecialOffer\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\LocalizedException;
use X247Commerce\SpecialOffer\Helper\Data;

class QuoteSubmitBefore implements ObserverInterface
{
    protected $helper;
    protected $resourceConnection;
    public function __construct(
        Data $helper,
        ResourceConnection $resourceConnection
    ){
         $this->helper = $helper;
         $this->resourceConnection = $resourceConnection;
    }


    public function execute(EventObserver $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        $isEnable = $this->helper->isEnable();
        $currentCoupon = $order->getCouponCode() ?? '';
        $specialCoupon = $this->helper->getSpecialCoupon() ?? '';
        if ($isEnable && $currentCoupon && strtolower($currentCoupon) == strtolower($specialCoupon)) {
            $usage = $this->validateUsage($order);
            if ($usage) {
                throw new LocalizedException(__('You can only use this coupon once!'));
            }
        }
    }


    private function validateUsage($order)
    {
        $coupon = $order->getCouponCode();
        $connection = $this->resourceConnection->getConnection();
        $orderTbl = $this->resourceConnection->getTableName('sales_order');
        $q = $connection->select()->from($orderTbl, 'customer_email')
                                    ->where('customer_email = "'.$order->getCustomerEmail().'"' )
                                    ->where( 'coupon_code = "'.$order->getCouponCode().'"')
                                    ->limit(1);
        return $connection->fetchOne($q);
    }

}
