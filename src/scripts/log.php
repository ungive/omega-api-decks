<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


function get_logger(string $name = 'common'): Logger
{
    $logger = new Logger($name);

    $handler = new StreamHandler('php://stderr', Logger::DEBUG);
    $formatter = new LineFormatter(null, 'Y-m-d\\TH:i:s.u', false, true);
    $handler->setFormatter($formatter);

    $logger->pushHandler($handler);
    return $logger;
}
