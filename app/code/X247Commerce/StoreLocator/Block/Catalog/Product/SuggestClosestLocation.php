<?php

namespace X247Commerce\StoreLocator\Block\Catalog\Product;

class SuggestClosestLocation extends \Magento\Framework\View\Element\Template
{

    protected $storeLocationContext;

    protected $registry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $storeLocationContext,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->storeLocationContext = $storeLocationContext;
    }

    /**
     * @return string
     */
    public function getSuggestClosestLocationAjaxUrl()
    {
        return $this->getUrl('x247_storelocator/product/suggestClosestLocation');
    }

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getCurrentProductSku()
    {
        $product = $this->getCurrentProduct();
        return $product->getSku();
    }

    public function getCurrentProductType()
    {
        $product = $this->getCurrentProduct();
        return $product->getProductType();
    }

    public function getCurrentStoreLocationId()
    {
        return $this->storeLocationContext->getStoreLocationId();
    }
}
