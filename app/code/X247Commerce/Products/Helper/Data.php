<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace X247Commerce\Products\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

/**
 * Catalog category helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data extends \Magento\Framework\Url\Helper\Data
{

    protected $swatchCollection;

    protected $swatchHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Swatches\Helper\Media $swatchHelper,
        \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $swatchCollection
    ) {
        $this->swatchHelper = $swatchHelper;
        $this->swatchCollection = $swatchCollection;
        parent::__construct($context);
    }


    public function getDataSwatchSponge( $optionId )
    {
        $item = $this->swatchCollection->create()
            ->addFieldtoFilter('option_id',$optionId)
            ->getFirstItem();

        if ( $item ) {
            return $this->swatchHelper->getSwatchAttributeImage('swatch_thumb', $item->getValue());
        }

        return false;
    }

}
