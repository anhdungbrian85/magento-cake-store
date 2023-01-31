<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\Nutritics\Model\Config\Source;

use X247Commerce\Nutritics\Helper\Config;

class Attribute implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => Config::NUTRITICS_CONFIG_API_ATTRIBUTE_IFC, 'label' => __('Ifc code')],
            ['value' => Config::NUTRITICS_CONFIG_API_ATTRIBUTE_CODE, 'label' => __('Code')]
        ];
    }

    public function toArray()
    {
        return [
            Config::NUTRITICS_CONFIG_API_ATTRIBUTE_IFC => __('Ifc code'),
            Config::NUTRITICS_CONFIG_API_ATTRIBUTE_CODE => __('Code')
        ];
    }
}

