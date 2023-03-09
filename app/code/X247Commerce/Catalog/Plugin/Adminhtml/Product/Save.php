<?php
namespace X247Commerce\Catalog\Plugin\Adminhtml\Product;

class Save
{
    public function beforeExecute(\Magento\Catalog\Controller\Adminhtml\Product\Save $subject)
    {
        $postData = $subject->getRequest()->getPostValue();
        $newCategoryData = $postData['product']['category_show'];
        $postData['product']['category_show_in_popup_crossell'] = json_encode($newCategoryData);

        $subject->getRequest()->setPostValue($postData);
    }
}