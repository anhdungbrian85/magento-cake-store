<?php

namespace X247Commerce\Products\Plugin\Magento\Review\Ui\DataProvider\Product\Form\Modifier;

class Review
{
    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterModifyMeta($subject, $result)
    {
        if (isset($result['review']['arguments']['data']['config'])) {
            $result['review']['arguments']['data']['config']['visible'] = false;
        }

        return $result;
    }
}
