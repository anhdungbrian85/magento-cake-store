<?php
/**
 * Copyright © Gigadesign. All rights reserved.
 */
declare(strict_types=1);

namespace Gigadesign\FixInvalidStateChange\Logger;

use Magento\Framework\Logger\Handler\Base;

use Monolog\Logger;

/**
 * Class Handler
 *
 * @author Mark van der Werf <info@gigadesign.nl>
 */
class Handler extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/invalidstate.log';
}
