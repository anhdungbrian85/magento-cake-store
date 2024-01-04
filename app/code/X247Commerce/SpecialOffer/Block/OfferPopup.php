<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\SpecialOffer\Block;

class OfferPopup extends \Magento\Framework\View\Element\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \X247Commerce\SpecialOffer\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    public function getSuccessCartMessage()
    {
        return $this->helper->getSuccessCartMessage();
    }

    public function getCouponCode()
    {
        return $this->helper->getSpecialCoupon() ?? '';
    }
    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }
}

