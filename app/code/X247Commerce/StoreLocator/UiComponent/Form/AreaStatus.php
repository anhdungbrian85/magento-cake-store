<?php

namespace X247Commerce\StoreLocator\UiComponent\Form;

use Magento\Framework\Option\ArrayInterface;

class AreaStatus implements ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {     
        return [['value' => 1, 'label' => 'WhiteListed'], ['value' => 0, 'label' => 'BlackListed']];
    }
}
