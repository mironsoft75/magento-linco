<?php
/**
 * Magento 2 Lyracons Core
 * Copyright (C) 2019  Lyracons
 *
 * This file included in Lyracons/Vega is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 * @author Lyracons Dev Team <devteam@lyracons.com>
 */

namespace Lyracons\Core\Logger;

use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger
{
    /**
     * @var boolean
     */
    private bool $debugMode = true;

    /**
     * Adds a log record at the DEBUG level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        if ($this->isDebugMode()) {
            parent::debug($message, $context);
        }
    }

    /**
     * Adds a log record at the INFO level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        if ($this->isDebugMode()) {
            parent::info($message, $context);
        }
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  string $message The log message
     * @param  array $context The log context
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        if ($this->isDebugMode()) {
            parent::notice($message, $context);
        }
    }

    /**
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    /**
     * @param bool $debugMode
     * @return Logger
     */
    public function setDebugMode(bool $debugMode): Logger
    {
        $this->debugMode = $debugMode;
        return $this;
    }
}
