<?php

namespace X247Commerce\Products\Plugin\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

class AttributeSet
{
    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterModifyMeta($subject, $result)
    {
        if (isset($result['attribute_set_id']['arguments']['data']['config'])) {
            $result['attribute_set_id']['arguments']['data']['config']['visible'] = false;
        }

        return $result;
    }
}
