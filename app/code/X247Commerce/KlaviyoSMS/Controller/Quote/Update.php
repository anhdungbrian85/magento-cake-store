<?php

namespace X247Commerce\KlaviyoSMS\Controller\Quote;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteRepository;

class Update extends Action
{

    protected $checkoutSession;

    protected $quoteRepository;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        QuoteRepository $quoteRepository
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute()
    {
        try {
            $value = $this->getRequest()->getParam('value');
            $inputName = $this->getRequest()->getParam('input_name');
            $quoteId = $this->checkoutSession->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);
            $quote->setData($inputName, (int)$value)->save();
            $data = [
                'status' => 200,
                'message' => __('Updated %s value!', $inputName)
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => 500,
                'message' => $e->getMessage()
            ];
        }

    }
}
