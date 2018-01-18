<?php

require __DIR__ . '/vendor/autoload.php';

define('APP_NAME', 'CoinHolding');
define('APP_LOG', '/Users/colonel32/Documents/coin_holdings/storage/logs/app.log');

$log = new Monolog\Logger(APP_NAME);
$log->pushHandler(new Monolog\Handler\StreamHandler(APP_LOG, Monolog\Logger::WARNING));
# $log->addWarning('Foo');


