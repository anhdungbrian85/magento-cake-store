<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);
namespace X247Commerce\Products\Block\Product\Renderer;

use Magento\Swatches\Block\Product\Renderer\Configurable as MageSwatchesConfigurable;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Store\Model\ScopeInterface;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;
use Magento\Framework\App\ObjectManager;
use Magento\Swatches\Model\SwatchAttributesProvider;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;

/**
 * Swatch renderer block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Configurable extends MageSwatchesConfigurable implements
    \Magento\Framework\DataObject\IdentityInterface
{

    /**
     * @var SwatchAttributesProvider
     */
    private $swatchAttributesProvider;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    protected $storeLocationContext;
    protected $checkoutSession;
    protected $categoryCollection;
    protected AttributeSetRepositoryInterface $attributeSetRepository;
    protected $productAttributeSetName;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param array $data
     * @param SwatchAttributesProvider|null $swatchAttributesProvider
     * @param UrlBuilder|null $imageUrlBuilder
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        StoreLocationContextInterface $storeLocationContext,
        CheckoutSession $checkoutSession,
        CategoryCollectionFactory $categoryCollection,
        AttributeSetRepositoryInterface $attributeSetRepository,
        array $data = [],
        SwatchAttributesProvider $swatchAttributesProvider = null,
        UrlBuilder $imageUrlBuilder = null
        
    ) {
    	parent::__construct(
            $context,
        	$arrayUtils,
        	$jsonEncoder,
         	$helper,
        	$catalogProduct,
        	$currentCustomer,
        	$priceCurrency,
        	$configurableAttributeData,
        	$swatchHelper,
        	$swatchMediaHelper,
        	$data,
        	$swatchAttributesProvider,
        	$imageUrlBuilder
        );
        $this->jsonEncoder = $jsonEncoder;
        $this->storeLocationContext  = $storeLocationContext ;
        $this->checkoutSession = $checkoutSession;
        $this->categoryCollection = $categoryCollection;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->swatchAttributesProvider = $swatchAttributesProvider
            ?: ObjectManager::getInstance()->get(SwatchAttributesProvider::class);
        $this->imageUrlBuilder = $imageUrlBuilder ?? ObjectManager::getInstance()->get(UrlBuilder::class);
        
    }

    public function getJsonConfigWith1HourCollection()
    {
    	$resultArr = json_decode($this->getJsonConfig(), true);
        $categoryUrlKey = "click-collect-1-hour";        
        $categoryClickCollect = $this->getCategoryByUrlKey($categoryUrlKey);
        $categoryClickCollectId = $categoryClickCollect->getEntityId();

        $allCategoryIds = [];
        $resultArr['skus'] = [];
        $hideId = [];
       	
        foreach ($this->getAllowProducts() as $product) {
            $isCake = strtolower($this->getProductAttributeSetName($product->getAttributeSetId())) == 'cake';
          	
            $resultArr['skus'][$product->getId()] = $product->getSku();
            if ($isCake && $product->getLeadDelivery() != 1) {
                $hideId[] = $product->getId();
            }

            $productCategoryIds = $product->getCategoryIds();
            $allCategoryIds = array_unique(array_merge($allCategoryIds, $productCategoryIds));
        }
        
        if (in_array($categoryClickCollectId, $allCategoryIds)) {
            foreach ($resultArr["attributes"] as &$attributes) {
                foreach ($attributes["options"] as &$value) {
                    $value["products"] = array_diff($value["products"], $hideId);
                    $value["products"] = array_values($value["products"]);
                }
            }
        }
        
        return $this->jsonEncoder->encode($resultArr);
    }

    protected function getProductAttributeSetName($attributeSetId)
    {

        if (!$this->productAttributeSetName) {
            $productAttributeSetName = $this->attributeSetRepository
                        ->get($attributeSetId)->getAttributeSetName();
            $this->productAttributeSetName = $productAttributeSetName;
        }
        return $this->productAttributeSetName;
    }

    public function getCategoryByUrlKey($urlKey)
    {
        $category = $this->categoryCollection
                                ->create()
                                ->addAttributeToFilter('url_key', $urlKey)
                                ->getFirstItem();
        return $category;
    }
}
