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
        $isOneHourCollection = $subject->getData('is_one_hour_collection');
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
            if ($product->getLeadDelivery() != 1 && $isOneHourCollection) {
                $hideId[] = $product->getId();
            }
        }

        if ($isOneHourCollection) {
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
   
}