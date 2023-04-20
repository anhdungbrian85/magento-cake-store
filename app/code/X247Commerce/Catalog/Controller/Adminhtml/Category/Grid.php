<?php
namespace X247Commerce\Catalog\Controller\Adminhtml\Category;

class Grid extends \Magento\Catalog\Controller\Adminhtml\Category\Grid
{
     /**
     * Grid Action
     * Display list of products related to current category
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $category = $this->_initCategory(true);
        if (!$category) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('catalog/*/', ['_current' => true, 'id' => null]);
        }
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                \X247Commerce\Catalog\Block\Adminhtml\Category\Tab\Product::class,
                'category.product.grid.popup'
            )->toHtml()
        );
    }
}