<?php
namespace X247Commerce\CustomUlmodProductinquiry\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Ulmod\Productinquiry\Model\ConfigData;
use Magento\Catalog\Helper\Image as HelperImage;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductinquiryLink extends \Ulmod\Productinquiry\Block\ProductinquiryLink
{
    public $scopeConfig;
    
    public function __construct(
        Context $context,
        ConfigData $configData,
        Registry $registry,
        HelperImage $imageHelper,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct(
            $context, 
            $configData,
            $registry,
            $imageHelper,
            $productRepository,
            $data
        );
        $this->scopeConfig = $scopeConfig;
    }

	public function isProductAllowed($product)
    {
        $valueInquiryConfig = $this->scopeConfig->getValue(
            'productinquiry/general/inquiry_for_category',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
        );
        $arrayConfigValue = explode(',', $valueInquiryConfig);
        $arraytCategoryOfProduct = $product->getCategoryIds();

        if (!$this->isInquiryForAllProducts() && count(array_intersect($arraytCategoryOfProduct, $arrayConfigValue)) > 0
            || $this->isInquiryForAllProducts()
        ) {
            return true;
        }
        return false;
    }
}