<?php

namespace X247Commerce\StoreLocator\Block\Catalog\Product;

class SuggestClosestLocation extends \Magento\Framework\View\Element\Template
{

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getSuggestClosestLocationAjaxUrl()
    {
        return $this->getUrl('x247_storelocator/product/suggestClosestLocation');
    }
}
