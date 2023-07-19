<?php

namespace X247Commerce\Checkout\Plugin;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteAddressValidator as OrigQuoteAddressValidator;
use Magento\Quote\Api\CartRepositoryInterface;

class QuoteAddressValidator
{
    protected CheckoutSession $checkoutSession;
    protected CartRepositoryInterface $cartRepository;

    public function __construct(
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param OrigQuoteAddressValidator $subject
     * @param CartInterface $cart
     * @param AddressInterface $address
     * @return array
     */
    public function beforeValidateForCart(
        OrigQuoteAddressValidator $subject,
        CartInterface $cart,
        AddressInterface $address
    )   {

        if ($cart->getCustomerIsGuest() && $cart->getCustomer()->getId()) {
            $cart->getCustomerIsGuest(0);
            $this->cartRepository->save($cart);
        }
        return [$cart, $address];
    }
}
