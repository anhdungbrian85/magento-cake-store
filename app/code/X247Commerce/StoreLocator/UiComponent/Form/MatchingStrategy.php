<?php

namespace X247Commerce\StoreLocator\UiComponent\Form;

use Magento\Framework\Option\ArrayInterface;

class MatchingStrategy implements ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {     
        return [['value' => 'Match Prefix', 'label' => 'Match Prefix'], ['value' => 'Match Exact', 'label' => 'Match Exact']];
    }
}
