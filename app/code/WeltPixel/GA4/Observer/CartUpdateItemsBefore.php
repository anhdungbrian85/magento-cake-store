<?php
namespace WeltPixel\GA4\Observer;

use Magento\Framework\Event\ObserverInterface;

class CartUpdateItemsBefore implements ObserverInterface
{
    /**
     * @var \WeltPixel\GA4\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;


    /**
     * @param \WeltPixel\GA4\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     */
    public function __construct(\WeltPixel\GA4\Helper\Data $helper,
                                \Magento\Checkout\Model\Session $_checkoutSession)
    {
        $this->helper = $helper;
        $this->_checkoutSession = $_checkoutSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isEnabled()) {
            return $this;
        }

        $infoDataObject = $observer->getData('info');
        $cart = $observer->getData('cart');

        $data = $infoDataObject->getData();

        foreach ($data as $itemId => $itemInfo) {
            $item = $cart->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || isset($itemInfo['qty']) && $itemInfo['qty'] == '0') {
                continue;
            }

            $item->setQtyBeforeChange($item->getQty());
        }

        return $this;
    }
}
