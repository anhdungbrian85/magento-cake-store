<?php
namespace X247Commerce\Catalog\Controller\Adminhtml\Category;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Category save controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Catalog\Controller\Adminhtml\Category\Save
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;


    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig = null,
        \Psr\Log\LoggerInterface $logger = null
    ) {
        parent::__construct(
            $context,
            $resultRawFactory,
            $resultJsonFactory,
            $layoutFactory,
            $dateFilter,
            $storeManager,
            $eavConfig,
            $logger
        );
        $this->storeManager = $storeManager;
        $this->eavConfig = $eavConfig ?: ObjectManager::getInstance()
            ->get(\Magento\Eav\Model\Config::class);
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);
    }

    /**
     * Category save
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $category = $this->_initCategory();

        if (!$category) {
            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
        }

        $categoryPostData = $this->getRequest()->getPostValue();

        $isNewCategory = !isset($categoryPostData['entity_id']);
        $categoryPostData = $this->stringToBoolConverting($categoryPostData);
        $categoryPostData = $this->imagePreprocessing($categoryPostData);
        $categoryPostData = $this->dateTimePreprocessing($category, $categoryPostData);
        $storeId = $categoryPostData['store_id'] ?? null;
        $store = $this->storeManager->getStore($storeId);
        $this->storeManager->setCurrentStore($store->getCode());
        $parentId = $categoryPostData['parent'] ?? null;
        if ($categoryPostData) {
            $category->addData($categoryPostData);
            if ($parentId) {
                $category->setParentId($parentId);
            }
            if ($isNewCategory) {
                $parentCategory = $this->getParentCategory($parentId, $storeId);
                $category->setPath($parentCategory->getPath());
                $category->setParentId($parentCategory->getId());
                $category->setLevel(null);
            }

            /**
             * Process "Use Config Settings" checkboxes
             */

            $useConfig = [];
            if (isset($categoryPostData['use_config']) && !empty($categoryPostData['use_config'])) {
                foreach ($categoryPostData['use_config'] as $attributeCode => $attributeValue) {
                    if ($attributeValue) {
                        $useConfig[] = $attributeCode;
                        $category->setData($attributeCode, null);
                    }
                }
            }

            $category->setAttributeSetId($category->getDefaultAttributeSetId());

            if (isset($categoryPostData['category_products'])
                && is_string($categoryPostData['category_products'])
                && !$category->getProductsReadonly()
            ) {
                $products = json_decode($categoryPostData['category_products'], true);
                $category->setPostedProducts($products);
            }

            if (isset($categoryPostData['category_products_popup'])
                && is_string($categoryPostData['category_products_popup'])
                && !$category->getProductsReadonly()
            ) {
                $products = json_decode($categoryPostData['category_products_popup'], true);
                $category->setPostedProductsPopup($products);
            }

            try {
                $this->_eventManager->dispatch(
                    'catalog_category_prepare_save',
                    ['category' => $category, 'request' => $this->getRequest()]
                );
                /**
                 * Check "Use Default Value" checkboxes values
                 */
                if (isset($categoryPostData['use_default']) && !empty($categoryPostData['use_default'])) {
                    foreach ($categoryPostData['use_default'] as $attributeCode => $attributeValue) {
                        if ($attributeValue) {
                            $category->setData($attributeCode, null);
                        }
                    }
                }

                /**
                 * Proceed with $_POST['use_config']
                 * set into category model for processing through validation
                 */
                $category->setData('use_post_data_config', $useConfig);

                $categoryResource = $category->getResource();
                if ($category->hasCustomDesignTo()) {
                    $categoryResource->getAttribute('custom_design_from')->setMaxValue($category->getCustomDesignTo());
                }

                $validate = $category->validate();
                if ($validate !== true) {
                    foreach ($validate as $code => $error) {
                        if ($error === true) {
                            $attribute = $categoryResource->getAttribute($code)->getFrontend()->getLabel();
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('The "%1" attribute is required. Enter and try again.', $attribute)
                            );
                        } else {
                            $this->messageManager->addErrorMessage(
                                __(
                                    'Something went wrong while saving the category.'
                                )
                            );
                            $this->logger->critical('Something went wrong while saving the category.');
                            $this->_getSession()->setCategoryData($categoryPostData);
                        }
                    }
                }

                $category->unsetData('use_post_data_config');

                $category->save();
                $this->messageManager->addSuccessMessage(__('You saved the category.'));
                // phpcs:disable Magento2.Exceptions.ThrowCatch
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e);
                $this->logger->critical($e);
                $this->_getSession()->setCategoryData($categoryPostData);
                // phpcs:disable Magento2.Exceptions.ThrowCatch
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the category.'));
                $this->logger->critical($e);
                $this->_getSession()->setCategoryData($categoryPostData);
            }
        }

        $hasError = (bool)$this->messageManager->getMessages()->getCountByType(
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );

        if ($this->getRequest()->getPost('return_session_messages_only')) {
            $category->load($category->getId());
            // to obtain truncated category name
            /** @var $block \Magento\Framework\View\Element\Messages */
            $block = $this->layoutFactory->create()->getMessagesBlock();
            $block->setMessages($this->messageManager->getMessages(true));

            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(
                [
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'category' => $category->toArray(),
                ]
            );
        }

        $redirectParams = $this->getRedirectParams($isNewCategory, $hasError, $category->getId(), $parentId, $storeId);

        return $resultRedirect->setPath(
            $redirectParams['path'],
            $redirectParams['params']
        );
    }
}