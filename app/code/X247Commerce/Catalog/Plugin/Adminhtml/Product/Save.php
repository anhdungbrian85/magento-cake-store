<?php
namespace X247Commerce\Catalog\Plugin\Adminhtml\Product;

class Save
{
    public function beforeExecute(\Magento\Catalog\Controller\Adminhtml\Product\Save $subject)
    {
        $postData = $subject->getRequest()->getPostValue();
        $newCategoryData = $postData['product']['category_show'];

        foreach ($newCategoryData['custom_field'] as $value) {
            $numPoint = 0;
            foreach ($newCategoryData['custom_field'] as $key => $value1) {
                if ($value1['select_field'] == $value['select_field']) {
                    $numPoint++;

                    if ( $numPoint > 1 ) {
                        unset($newCategoryData['custom_field'][$key]);
                        $numPoint--;
                    }
                }
            }
        }

        $postData['product']['category_show_in_popup_crossell'] = json_encode($newCategoryData);

        $subject->getRequest()->setPostValue($postData);
    }
}