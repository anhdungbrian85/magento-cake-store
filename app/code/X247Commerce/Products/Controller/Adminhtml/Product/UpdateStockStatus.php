<?php

namespace X247Commerce\Products\Controller\Adminhtml\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use Magento\Framework\Controller\ResultFactory;

class UpdateStockStatus extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    protected $productRepository;

    protected $locatorSourceResolver;

    protected $adminSession;

    protected $adminSourceCollectionFactory;

    public function __construct(
        \X247Commerce\StoreLocatorSource\Model\ResourceModel\AdminSource\CollectionFactory $adminSourceCollectionFactory,
        \Magento\Backend\Model\Auth\Session $adminSession,
        LocatorSourceResolver $locatorSourceResolver,
        ProductRepositoryInterface $productRepository,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->adminSession = $adminSession;
        $this->adminSourceCollectionFactory = $adminSourceCollectionFactory;
    }

    public function execute()
    {
        $roleData = $this->adminSession->getUser()->getRole()->getData();
        $userData = $this->adminSession->getUser()->getData();
        $sourceCodes = [];
        if ((int) $roleData['role_id'] != 1) {
            $adminSourceCollection = $this->adminSourceCollectionFactory->create()->addFieldToFilter('user_id', ['eq' => $userData['user_id']])->load();
            foreach ($adminSourceCollection->getItems() as $adminSource) {
                $sourceCodes[] = $adminSource->getSourceCode();
            }
        }
        try {
            foreach ($sourceCodes as $sourceCode) {
                $productId = $this->getRequest()->getParam('product_id');
                $stockStatus = $this->getRequest()->getParam('status');

                $product = $this->productRepository->getById($productId);
                $childProductIds = $product->getTypeInstance()->getUsedProductIds($product);
                $childProductSkus = [];

                foreach ($childProductIds as $childProductId) {
                    $childProduct = $this->productRepository->getById($childProductId);
                    $stockStatusItem = $this->locatorSourceResolver->checkStockStatusBySourceCodeAndSku($childProduct->getSku(), $sourceCode);
                    if (
                        $stockStatusItem != \X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver::NOT_HAVE_STOCK_STATUS
                        && $stockStatusItem != $stockStatus
                    ) {
                        $childProductSkus[] = $childProduct->getSku();
                    }
                }
                if (!empty($childProductSkus)) {
                    $this->locatorSourceResolver->updateStockStatusBySourceCode($childProductSkus, $sourceCode, $stockStatus);
                }
            }
            $this->messageManager->addSuccessMessage(__('You updated stock status of child products.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the category.'));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }


}
