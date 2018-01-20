<?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use CryptoClient\BittrexClient;
use CryptoClient\BittrexHoldings;
use CryptoClient\CryptopiaClient;
use CryptoClient\CryptoCompareClient;

$config = parse_ini_file('../config.ini');

setlocale(LC_MONETARY, 'en_US');

define('APP_NAME', 'CoinHolding');
define('APP_LOG', '/Users/colonel32/Documents/coin_holdings/storage/logs/app.log');

$log = new Monolog\Logger(APP_NAME);
$log->pushHandler(new Monolog\Handler\StreamHandler(APP_LOG, Monolog\Logger::DEBUG));

$client = new Client([
  // You can set any number of default request options.
  'timeout'  => 2.0,
]);

$bittrex = new BittrexClient($config['BITTREX_API_KEY'], $config['BITTREX_API_SECRECT'], $client);

#$cryptopia = new CryptopiaClient($config['CRYPTOPIA_API_KEY'], $config['CRYPTOPIA_API_SECRET'], $client);

$cryptoCompare = new CryptoCompareClient($client, $log);

