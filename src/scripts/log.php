<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


function get_logger(string $name = 'common', string $filename = 'server.log'): Logger
{
    if (!file_exists(LOG_DIR))
        mkdir(LOG_DIR, 0775);

    $logger = new Logger($name);
    $logger->pushHandler(new StreamHandler(LOG_DIR . "/" . $filename, Logger::DEBUG));
    $logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));

    return $logger;
}
