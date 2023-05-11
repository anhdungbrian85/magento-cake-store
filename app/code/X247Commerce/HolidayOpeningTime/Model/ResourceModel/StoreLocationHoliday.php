<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\HolidayOpeningTime\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StoreLocationHoliday extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('store_location_holiday', 'id');
    }


}

