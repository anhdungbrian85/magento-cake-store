<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace X247Commerce\Staging\Model\Update;

use Magento\Staging\Api\Data\UpdateInterface;

/**
 * Validate a staging update entity data.
 */
class Validator extends \Magento\Staging\Model\Update\Validator
{
    public function validateCreate(UpdateInterface $entity)
    {
        $this->validateUpdate($entity);
    }
}
