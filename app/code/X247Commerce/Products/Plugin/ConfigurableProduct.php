<?php    
namespace X247Commerce\Products\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Json\EncoderInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;

class ConfigurableProduct
{
    protected $productCollection;
    protected $jsonEncoder;
    protected $storeLocationContext;
    protected $checkoutSession;
    protected $categoryCollection;
    protected AttributeSetRepositoryInterface $attributeSetRepository;
    protected $productAttributeSetName;

    public function __construct(
        ProductCollection $productCollection,
        EncoderInterface $jsonEncoder,
        StoreLocationContextInterface $storeLocationContext,
        CheckoutSession $checkoutSession,
        CategoryCollectionFactory $categoryCollection,
        AttributeSetRepositoryInterface $attributeSetRepository
    )
    {
        $this->productCollection = $productCollection;
        $this->jsonEncoder = $jsonEncoder;
        $this->storeLocationContext  = $storeLocationContext ;
        $this->checkoutSession = $checkoutSession;
        $this->categoryCollection = $categoryCollection;
        $this->attributeSetRepository = $attributeSetRepository;
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

    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject, $result
    ) {
        $resultArr = json_decode($result, true);
        $deliveryType = $this->storeLocationContext->getDeliveryType() ?? $this->checkoutSession->getDeliveryType();
        $categoryUrlKey = "click-collect-1-hour";        
        $categoryClickCollect = $this->getCategoryByUrlKey($categoryUrlKey);
        $categoryClickCollectId = $categoryClickCollect->getEntityId();
        $allCategoryIds = [];
        $resultArr['skus'] = [];
        $hideId = [];
        $characterLimit = [];
        foreach ($subject->getAllowProducts() as $product) {
            $isCake = strtolower($this->getProductAttributeSetName($product->getAttributeSetId())) == 'cake';
          
            $resultArr['skus'][$product->getId()] = $product->getSku();
            if ($product->getCharacterLimit()) {
                $characterLimit['character_limit'][$product->getId()] = $product->getCharacterLimit();
            }
            if ($isCake && $product->getLeadDelivery() != 1) {
                $hideId[] = $product->getId();
            }
            $productCategoryIds = $product->getCategoryIds();
            $allCategoryIds = array_merge($allCategoryIds, $productCategoryIds);
        }

        if ($deliveryType == 2 && in_array($categoryClickCollectId, $allCategoryIds)) {
            foreach ($resultArr["attributes"] as &$attributes) {
                foreach ($attributes["options"] as &$value) {
                    $value["products"] = array_diff($value["products"], $hideId);
                    $value["products"] = array_values($value["products"]);
                }
            }
        }
        
        $config = array_merge($resultArr, $characterLimit);
        return $this->jsonEncoder->encode($config);;
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