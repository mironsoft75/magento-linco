<?php
/**
 * Magento 2 Lyracons Core
 * Copyright (C) 2019  Lyracons
 *
 * This file included in Lyracons/Core is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 * @author Lyracons Dev Team <devteam@lyracons.com>
 */

namespace Lyracons\Core\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonologLogger;

class Logger extends Base
{

    const FILE_NAME = '/var/log/lyracons-core.log';

    /**
     * @var string
     */
    protected $fileName = self::FILE_NAME;

    /**
     * @var
     */
    protected $loggerType = MonologLogger::DEBUG;

}