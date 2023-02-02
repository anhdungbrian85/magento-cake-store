<?php
/**
 * Copyright © HTCSoft, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace X247Commerce\Theme\Block\Html;

use Magento\Framework\View\Element\Template;

class BestSellerHome extends Template
{

    protected $productStatus;

    protected $productsFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelp,
        \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $bestSellersCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $productsFactory,
        array $data = []
    )
    {    
        $this->imageHelper = $imageHelper;
        $this->_productCollectionFactory = $productCollectionFactory;  
        $this->priceHelp = $priceHelp;
        $this->bestSellersCollectionFactory = $bestSellersCollectionFactory;
        $this->storeManager = $storeManager;
        $this->productStatus = $productStatus;
        $this->productsFactory = $productsFactory;
        parent::__construct($context, $data);
    }

    public function getProductCollection()
    {
        $productIds = [];
        $bestSellers = $this->bestSellersCollectionFactory->create()->setPeriod('monthly');
        $storeId = $this->storeManager->getStore()->getId();

        foreach ($bestSellers as $product) {
            $productIds[] = $product->getProductId();
        }

        $collection = $this->_productCollectionFactory->create();
        $collection->addIdFilter($productIds)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', array('eq' => "configurable"))
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->addStoreFilter($storeId)->setPageSize(8);

        if ( count( $collection ) > 0 ) {
            return $collection;
        }


        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addStoreFilter($storeId)
            ->addAttributeToFilter('type_id', array('eq' => "configurable"))
            ->setOrder('created_at','DESC')
            ->setPageSize(8);

        return $collection;
    }

    public function getImageUrl($product)
    {
        return $this->imageHelper->init($product, 'product_page_image_large')->getUrl();
    }

    public function priceFormat($price)
    {
        return $this->priceHelp->currency($price, true, false);
    }
}
