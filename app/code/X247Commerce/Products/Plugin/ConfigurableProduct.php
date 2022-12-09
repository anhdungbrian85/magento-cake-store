<?php    
namespace X247Commerce\Products\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Json\EncoderInterface;

class ConfigurableProduct
{
    protected $productCollection;
    protected $jsonEncoder;

    public function __construct(
        ProductCollection $productCollection,
        EncoderInterface $jsonEncoder
    )
    {
        $this->productCollection = $productCollection;
        $this->jsonEncoder = $jsonEncoder;
    }

    public function afterGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject, $result
    ) {
        $resultArr = json_decode($result, true);
        $characterLimit = [];
        foreach ($subject->getAllowProducts() as $product) {
            if ($product->getCharacterLimit()) {
                $characterLimit['character_limit'][$product->getId()] = $product->getCharacterLimit();
            }
        }
        $config = array_merge($resultArr, $characterLimit);
        return $this->jsonEncoder->encode($config);;
    }
}