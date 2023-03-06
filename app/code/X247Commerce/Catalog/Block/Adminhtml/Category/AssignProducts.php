<?php
namespace X247Commerce\Catalog\Block\Adminhtml\Category;

class AssignProducts extends \Magento\Catalog\Block\Adminhtml\Category\AssignProducts
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'X247Commerce_Catalog::catalog/category/edit/assign_products.phtml';

        /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \X247Commerce\Catalog\Block\Adminhtml\Category\Tab\Product::class,
                'category.product.grid.popup'
            );
        }
        return $this->blockGrid;
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        $products = $this->getCategory()->getProductsPopupPosition();
        if (!empty($products)) {
            return $this->jsonEncoder->encode($products);
        }
        return '{}';
    }
}