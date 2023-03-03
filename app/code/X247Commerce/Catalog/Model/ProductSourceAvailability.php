<?php
namespace X247Commerce\Catalog\Model;

use Magento\Catalog\Model\Product;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;

class ProductSourceAvailability
{
    protected $_product = null;

    protected $_coreRegistry = null;

    private $getQuantityInformationPerSource;

    public function __construct(
        \Magento\Framework\Registry $registry,
        GetSourceItemsDataBySku $getQuantityInformationPerSource
    ) {
        $this->_coreRegistry = $registry;
        $this->getQuantityInformationPerSource = $getQuantityInformationPerSource;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    public function getQuantityInformationForProduct($productSku)
    {
        return $this->getQuantityInformationPerSource->execute($productSku);
    }
}
