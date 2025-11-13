<?php

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;

/**
 * Logger Class
 * Wrapper around Monolog for application logging
 */
class Logger
{
    private static ?MonologLogger $instance = null;

    /**
     * Get logger instance (Singleton)
     *
     * @return MonologLogger
     */
    public static function getInstance(): MonologLogger
    {
        if (self::$instance === null) {
            self::$instance = new MonologLogger('crawler');

            // Log to file
            $logFile = __DIR__ . '/../logs/crawler.log';
            self::$instance->pushHandler(new StreamHandler($logFile, Level::Debug));
        }

        return self::$instance;
    }
}
