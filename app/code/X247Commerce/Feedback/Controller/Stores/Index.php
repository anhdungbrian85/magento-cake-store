<?php
namespace X247Commerce\Feedback\Controller\Stores;

use Magento\Framework\App\Action\Context;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection as LocationFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_collection;
    private $locationCollection;
    private $locationCollectionFactory;

    public function __construct(
        Context $context,
        LocationFactory $locationCollectionFactory,
    ) {
        $this->locationCollectionFactory = $locationCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $collection = $this->locationCollectionFactory->getData();
            $response = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData($collection);
            return $response;
        }
    }
}