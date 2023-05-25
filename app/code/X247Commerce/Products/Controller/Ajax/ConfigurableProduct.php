<?php

namespace X247Commerce\Products\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class ConfigurableProduct extends Action
{
    protected $resultFactory;
    protected $logger;
    protected $productRepository;
    protected $productFactory;
    protected $jsonEncoder;
    protected $storeLocationContext;
    protected $checkoutSession;
    protected $blockConfigurable;
	protected $formKeyValidator;

    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        ProductFactory $productFactory,
        EncoderInterface $jsonEncoder,
        StoreLocationContextInterface $storeLocationContext,
        CheckoutSession $checkoutSession,
		FormKeyValidator $formKeyValidator,
         \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $blockConfigurable
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
		$this->formKeyValidator = $formKeyValidator;
        $this->jsonEncoder = $jsonEncoder;
        $this->storeLocationContext  = $storeLocationContext ;
        $this->checkoutSession = $checkoutSession;
        $this->blockConfigurable = $blockConfigurable;

    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultData = [];
        return $result->setData(['jsonConfig' => $this->getJsonConfig()]);
    }

    public function getJsonConfig()
    {
        $productId = $this->getRequest()->getParam('productId', false);
        $clickCollect = $this->getRequest()->getParam('clickCollect', false);
        $this->checkoutSession->setClickCollect($clickCollect);
        if ($productId) {
            $product = $this->productFactory->create()->load($productId);
            return $this->blockConfigurable
                ->setProduct($product)
                ->setData('is_one_hour_collection', $clickCollect)
                ->getJsonConfig();
        }
        return '';
    }
}
