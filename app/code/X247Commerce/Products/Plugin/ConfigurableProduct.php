<?php    
namespace X247Commerce\Products\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Json\EncoderInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;

class ConfigurableProduct
{
    protected $productCollection;
    protected $jsonEncoder;
    protected $storeLocationContext;
    protected $checkoutSession;
    protected $categoryCollection;
    protected $redirect;
    protected $catalogHelper;

    public function __construct(
        ProductCollection $productCollection,
        EncoderInterface $jsonEncoder,
        StoreLocationContextInterface $storeLocationContext,
        CheckoutSession $checkoutSession,
        CategoryCollectionFactory $categoryCollection,
        RedirectInterface $redirect,
        CatalogHelper $catalogHelper
    )
    {
        $this->productCollection = $productCollection;
        $this->jsonEncoder = $jsonEncoder;
        $this->storeLocationContext  = $storeLocationContext ;
        $this->checkoutSession = $checkoutSession;
        $this->categoryCollection = $categoryCollection;
        $this->redirect = $redirect;
        $this->catalogHelper = $catalogHelper;
    }

    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject, $result
    ) {
        
        $resultArr = json_decode($result, true);
        $deliveryType = $this->storeLocationContext->getDeliveryType() ?? $this->checkoutSession->getDeliveryType();
        $clickCollect = $this->checkoutSession->getClickCollect();
        $categoryUrlKey = "click-collect-1-hour";        
        $categoryClickCollect = $this->getCategoryByUrlKey($categoryUrlKey);
        $categoryClickCollectId = $categoryClickCollect->getEntityId();
        $allCategoryIds = [];
        $resultArr['skus'] = [];
        $resultArr['lead_delivery'] = [];
        $hideId = [];
        $characterLimit = [];
        foreach ($subject->getAllowProducts() as $product) {
          
            $resultArr['skus'][$product->getId()] = $product->getSku();
            $resultArr['lead_delivery'][$product->getId()] = $product->getLeadDelivery();
            if ($product->getCharacterLimit()) {
                $characterLimit['character_limit'][$product->getId()] = $product->getCharacterLimit();
            }
            if ($product->getLeadDelivery() != 1) {
                $hideId[] = $product->getId();
            }
            $productCategoryIds = $product->getCategoryIds();
            $allCategoryIds = array_merge($allCategoryIds, $productCategoryIds);
        }

        if (($deliveryType == 2 && in_array($categoryClickCollectId, $allCategoryIds)) || ($deliveryType != 2 && $clickCollect)) {
            foreach ($resultArr["attributes"] as &$attributes) {
                foreach ($attributes["options"] as &$value) {
                    $value["products"] = array_diff($value["products"], $hideId);
                    $value["products"] = array_values($value["products"]);
                }
            }
        }
        
        $config = array_merge($resultArr, $characterLimit);
        return $this->jsonEncoder->encode($config);
    }
    public function getCategoryByUrlKey($urlKey)
    {
        $category = $this->categoryCollection
                                ->create()
                                ->addAttributeToFilter('url_key', $urlKey)
                                ->getFirstItem();
        return $category;
    }
    public function getRefererUrl()
    {
        return $redirectUrl = $this->redirect->getRefererUrl();
    }

    public function getBreadcrumbPath() {
        return $this->catalogHelper->getBreadcrumbPath();
    }
    /**
     * Return current category object
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCategory()
    {
        return $this->catalogHelper->getCategory();
    }
}