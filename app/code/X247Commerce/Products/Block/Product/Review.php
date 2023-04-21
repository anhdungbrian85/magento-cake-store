<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace X247Commerce\Products\Block\Product;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

/**
 * Product Review Tab
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Review extends \Magento\Review\Block\Product\Review
{

    /**
     * Get current product id
     *
     * @return null|int
     */
    public function getProduct()
    {
        $product = $this->_coreRegistry->registry('product');
        return $product;
    }


}
