<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\Yext\Model\Config\Source;

use X247Commerce\Yext\Helper\Config;

class Mode implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => Config::YEXT_CONFIG_LIVE_MODE, 'label' => __('Live')],
            ['value' => Config::YEXT_CONFIG_SANDBOX_MODE, 'label' => __('Sandbox')]
        ];
    }

    public function toArray()
    {
        return [
            Config::YEXT_CONFIG_LIVE_MODE => __('Live'),
            Config::YEXT_CONFIG_SANDBOX_MODE => __('Sandbox')
        ];
    }
}

