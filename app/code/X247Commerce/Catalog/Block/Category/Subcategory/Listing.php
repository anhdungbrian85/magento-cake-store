<?php

namespace X247Commerce\Catalog\Block\Category\Subcategory;

class Listing extends \Magento\Framework\View\Element\Template
{

    protected $categoryCollectionFactory;

    protected $storeManager;

    protected $layerResolver;

    protected $placeholderImage;

    protected $urlThumbPlaceholder = null;

    protected $mediaPath = null;

    protected $urlRewritesRegenerator;



    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\View\Asset\PlaceholderFactory $placeholderImage,
        \Magento\CatalogUrlRewrite\Model\Category\CurrentUrlRewritesRegenerator $urlRewritesRegenerator,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->layerResolver = $layerResolver->get();
        $this->placeholderImage = $placeholderImage;
        $this->urlRewritesRegenerator = $urlRewritesRegenerator;
    }


    public function getCurrentCategory()
    {
        return $this->layerResolver->getCurrentCategory();
    }

    public function getSubcategories(){
        $category = $this->getCurrentCategory();
        $strCategories = $category->getAllChildren();
        $arrCategories = explode(',', $strCategories);
        unset($arrCategories[0]);
        $collection = $this->categoryCollectionFactory->create()
        ->addAttributeToSelect('*')
        ->addFieldToFilter('entity_id', ['in' => $arrCategories]);
        return $collection;
    }

    public function getThumbnailUrl($category){

        if($category->getThumbnail()){
            return $this->getMediaUrl().'catalog/category/'.$category->getThumbnail();
        }
        return $this->getPlaceholderThumbnail();
    }


    protected function getPlaceholderThumbnail(){
        if(!$this->urlThumbPlaceholder){
            $this->urlThumbPlaceholder = $this->placeholderImage->create(['type' => 'thumbnail'])->getUrl();
        }
        return $this->urlThumbPlaceholder;
    }

    public function getMediaUrl(){
        if(!$this->mediaPath){
            $store = $this->storeManager->getStore();
            $this->mediaPath =  $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        }
        return $this->mediaPath;
    }

    public function getCategoryUrl($category){
        $storeId = $this->storeManager->getStore()->getId();
        return $this->urlRewritesRegenerator->generate($storeId, $category);
    }


}