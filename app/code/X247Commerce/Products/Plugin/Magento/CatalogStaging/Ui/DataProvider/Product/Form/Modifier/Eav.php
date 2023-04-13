<?php

namespace X247Commerce\Products\Plugin\Magento\CatalogStaging\Ui\DataProvider\Product\Form\Modifier;

class Eav
{
    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterModifyMeta($subject, $result)
    {
        if (isset($result['product-details']['arguments']['data']['config'])) {
            $result['product-details']['arguments']['data']['config']['visible'] = false;
        }

        return $result;
    }
}
