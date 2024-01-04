<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\SpecialOffer\Controller\Index;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\JsonFactory;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use X247Commerce\SpecialOffer\Helper\Data as Helper;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

/**
 * Class Index
 */
class Post extends Action implements HttpPostActionInterface
{
    protected $resultJsonFactory;
    protected $checkoutSession;
    protected $helper;

    public function __construct(
        Context                    $context,
        Session             $checkoutSession,
        JsonFactory $resultJsonFactory,
        Helper                     $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $result['result'] = false;

        /** Add Customer Data */
        if ($this->checkoutSession->getHasUseCakeboxOffer()) {
            $result['result'] = true;
            $this->checkoutSession->setHasUseCakeboxOffer(null);
        }
        $resultJson->setData($result);
        return $resultJson;
    }
}
