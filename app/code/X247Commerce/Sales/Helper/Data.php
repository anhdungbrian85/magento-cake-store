<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\Sales\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;


/**
 * Sales admin helper.
 */
class Data extends AbstractHelper
{
    CONST XML_PATH_ENABLED_SORTING_COLUMNS = 'admin_order_grid/sorting/enable_fields';

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getEnableSortingColumns()
    {
        return  $this->scopeConfig->getValue(self::XML_PATH_ENABLED_SORTING_COLUMNS, ScopeInterface::SCOPE_STORE);
    }
}
