<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace X247Commerce\Checkout\Plugin\Amasty\CheckoutCore\Model\Quote;

use Magento\Quote\Api\CartRepositoryInterface;
use \Psr\Log\LoggerInterface;

class CheckoutInitialization
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        LoggerInterface $logger
    ) {     
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    public function aroundSaveInitial(
        \Amasty\CheckoutCore\Model\Quote\CheckoutInitialization $subject,
        \Closure $proceed,
        $quote
    ) {
        try {
            $quote->setItems([]);
            $this->quoteRepository->save($quote);
        } catch(\Exception $e) {
            $this->logger->error("Error occurred: " . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
        }
    }
}
