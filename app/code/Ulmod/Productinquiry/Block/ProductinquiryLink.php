<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Ulmod\Productinquiry\Model\ConfigData;
use Magento\Catalog\Helper\Image as HelperImage;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductinquiryLink extends Template
{
    /**
     * @var ConfigData
     */
    public $configData;
    
    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var HelperImage
     */
    protected $imageHelper;
    
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    
    /**
     * @param Context $context
     * @param ConfigData $configData
     * @param Registry $registry
     * @param HelperImage $imageHelper
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigData $configData,
        Registry $registry,
        HelperImage $imageHelper,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->configData = $configData;
        $this->registry = $registry;
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }
    
    /**
     * Get product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }
    
    /**
     * Get product inquiry link
     *
     * @param int $productId
     * @return string
     */
    public function getLink($productId)
    {
        $params = [
            'id' => $productId
        ];
        
        return $this->getUrl(
            'productinquiry/index/new',
            ['_query' => $params]
        );
    }
    
    /**
     * Get extention status config value
     *
     * @return string
     */
    public function isExtensionEnabled()
    {
        return $this->configData->isExtensionEnabled();
    }

    /**
     * Get form type config value
     *
     * @return string
     */
    public function getFormType()
    {
        return $this->configData->getFormType();
    }

    /**
     * Get inquiry for all status config value
     *
     * @return string
     */
    public function isInquiryForAllProducts()
    {
        return $this->configData->isInquiryForAllProducts();
    }
    
    /**
     * Check if inquiry is enabled on selected categories
     *
     * @return bool
     */
    public function isEnableInquiryOnCategory()
    {
        return $this->configData->isEnableInquiryOnCategory();
    }
    
    /**
     * Get inquiry for link text config value
     *
     * @return string
     */
    public function getInquiryLinkText()
    {
        return $this->configData->getInquiryLinkText();
    }
 
    /**
     * Get item image
     *
     * @param int $productId
     * @return string
     */
    public function getItemImage($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return 'product not found';
        }
        $imageUrl = $this->imageHelper->init($product, 'product_thumbnail_image')
            ->getUrl();
        
        return $imageUrl;
    }

    /**
     * Get link text
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getLinkText($product)
    {
        $linkText = $this->getInquiryLinkText();
        if (!$this->isInquiryForAllProducts() && $product->getData('um_productinquiry')) {
            $linkText = $product->getData('um_productinquiry_text');
        }
        return $linkText;
    }

    /**
     * Return form type css class
     *
     * @return string
     */
    public function getFormTypeClass()
    {
        $cssClass = 'new_page';
        if ($this->getFormType() == 'popup') {
            $cssClass = 'popup';
        }
        return $cssClass;
    }

    /**
     * Check if new form page type
     *
     * @return bool
     */
    public function isNewPageForm()
    {
        if ($this->getFormType() == 'new_page') {
            return true;
        }
        return false;
    }

    /**
     * Check if product allowed
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isProductAllowed($product)
    {
        if (!$this->isInquiryForAllProducts() && $product->getData('um_productinquiry')
            || $this->isInquiryForAllProducts()
        ) {
            return true;
        }
        return false;
    }
}
