<?php

namespace WeltPixel\GA4\Plugin;

use Magento\Checkout\Model\Cart as CustomerCart;

class CartUpdateItemOptions
{
    /**
     * @var \WeltPixel\GA4\Helper\Data
     */
    protected $helper;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @param \WeltPixel\GA4\Helper\Data $helper
     * @param CustomerCart $cart
     */
    public function __construct(
        \WeltPixel\GA4\Helper\Data $helper,
        CustomerCart $cart
    ) {
        $this->helper = $helper;
        $this->cart = $cart;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\UpdateItemOptions $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(\Magento\Checkout\Controller\Cart\UpdateItemOptions $subject)
    {
        if (!$this->helper->isEnabled()) {
            return;
        }

        $id = (int)$subject->getRequest()->getParam('id');
        $quoteItem = $this->cart->getQuote()->getItemById($id);

        if ($quoteItem) {
            $quoteItem->setQtyBeforeChange($quoteItem->getQty());
        }
    }
}
