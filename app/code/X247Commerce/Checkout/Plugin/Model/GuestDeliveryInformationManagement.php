<?php
namespace X247Commerce\Checkout\Plugin\Model;

class GuestDeliveryInformationManagement
{
    public function beforeUpdate(\Amasty\CheckoutDeliveryDate\Model\GuestDeliveryInformationManagement $subject, $cartId, $date, $time = -1, $comment = '')
    {
        $date = \DateTime::createFromFormat("d/m/Y", $date)->format("m/d/Y");

        return [$cartId, $date, $time, $comment];
    }
}