<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\Yext\Model\Config\Source;

use X247Commerce\Yext\Helper\Config;

class Type implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => Config::YEXT_CONFIG_KNOWNLEADGE_TYPE, 'label' => __('Knownledge')],
            ['value' => Config::YEXT_CONFIG_LIVE_TYPE, 'label' => __('Live')]
        ];
    }

    public function toArray()
    {
        return [
            Config::YEXT_CONFIG_KNOWNLEADGE_TYPE => __('Knownledge'),
            Config::YEXT_CONFIG_LIVE_TYPE => __('Live')
        ];
    }
}

