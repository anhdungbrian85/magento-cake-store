<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\Catalog\Observer;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Employ additional authorization logic when a category is saved.
 */
class CategoryLoad implements ObserverInterface
{   

    protected $categoryProductPopupProvider;
    
    public function __construct(
        \X247Commerce\Catalog\Model\ResourceModel\CategoryProductPopupProvider $categoryProductPopupProvider
    ) {
        $this->categoryProductPopupProvider = $categoryProductPopupProvider;
    }
    /**
     * @inheritDoc
     *
     * @throws AuthorizationException
     */
    public function execute(Observer $observer)
    {
        /** @var CategoryInterface $category */
        $category = $observer->getEvent()->getDataObject();
        $this->setProductsPopupPosition($category);
    }

    /**
     * Retrieve array of product id's for category
     *
     * The array returned has the following format:
     * array($productId => $position)
     *
     * @return $this
     */
    private function setProductsPopupPosition($category)
    {
        if (!$category->getId()) {
            return [];
        }

        $array = $category->getData('products_popup_position');
        if ($array === null) {
            $array = $this->categoryProductPopupProvider->getProductsPopupPosition($category);
            $category->setData('products_popup_position', $array);
        }
        return $this;
    }
}
