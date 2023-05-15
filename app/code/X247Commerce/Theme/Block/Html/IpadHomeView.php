<?php

namespace X247Commerce\Theme\Block\Html;

use \Magento\Catalog\Block\Category\View;

class IpadHomeView extends View implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;

    protected $categoryCollectionFactory;

    protected $categoryRepository;

    protected $_storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \X247Commerce\Theme\Helper\CategoryHelper $categoryHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
        $this->_categoryHelper = $categoryHelper;
        $this->_catalogLayer = $layerResolver->get();
        $this->_coreRegistry = $registry;
        $this->categoryRepository = $categoryRepository;
        $this->_storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $category = $this->getCurrentCategory();
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb(
                'category'.$category->getId(),
                [
                    'label' => __($category->getName()),
                ]
            );
        }
        return $this;
    }

    public function getCurrentCategory(){
        $categoryId = $this->getCateIdAvailable();
        return $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
    }

    protected function getCateIdAvailable()
    {
        return $this->_categoryHelper->getCateIDIpadHomeView() ?? null;
    }
}