<?php

namespace X247Commerce\StoreLocator\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class ReselectLocation extends \Magento\Framework\App\Action\Action
{

    protected $locationContext;

    public function __construct(
        Context $context,
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $locationContext
    ) {
        parent::__construct($context);
        $this->locationContext = $locationContext;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->getRequest()->getParams('location_id')) {
            $this->locationContext->setStoreLocationId($this->getRequest()->getParams('location_id'));
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
