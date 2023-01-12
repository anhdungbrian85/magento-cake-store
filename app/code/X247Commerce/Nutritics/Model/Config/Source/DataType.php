<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\Nutritics\Model\Config\Source;

use X247Commerce\Nutritics\Helper\Config;

class DataType implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => Config::NUTRITICS_CONFIG_API_TYPE_FOOD, 'label' => __('Food')],
            ['value' => Config::NUTRITICS_CONFIG_API_TYPE_RECIPE, 'label' => __('Recipe')]
            //@todo activity, menu
        ];
    }

    public function toArray()
    {
        return [
            Config::NUTRITICS_CONFIG_API_TYPE_FOOD => __('Food'),
            Config::NUTRITICS_CONFIG_API_TYPE_RECIPE => __('Recipe')
        ];
    }
}

