<?php
namespace X247Commerce\Catalog\Plugin\Adminhtml\Product;

class Save
{
    public function beforeExecute(\Magento\Catalog\Controller\Adminhtml\Product\Save $subject)
    {
        $postData = $subject->getRequest()->getPostValue();
        $oldCategoryData = $postData['product']['category_show_in_popup_crossell'];
        if ($oldCategoryData != '') {
            $newCategoryData = '';

            foreach ($oldCategoryData as $item) {
                $newCategoryData .= $item . ',';
            }

            $newCategoryData = rtrim($newCategoryData, ","); 
            $postData['product']['category_show_in_popup_crossell'] = $newCategoryData;
        }
        
        $subject->getRequest()->setPostValue($postData);
    }
}