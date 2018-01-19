<?php

require __DIR__ . '/vendor/autoload.php';

setlocale(LC_MONETARY, 'en_US');

define('APP_NAME', 'CoinHolding');
define('APP_LOG', '/Users/colonel32/Documents/coin_holdings/storage/logs/app.log');

define('TEST_COINLIST_FILE', '/Users/colonel32/Documents/coin_holdings/storage/data/coinlist.json');
define('TEST_USD_PRICE_FILE', '/Users/colonel32/Documents/coin_holdings/storage/data/USD_price.json');

$log = new Monolog\Logger(APP_NAME);
$log->pushHandler(new Monolog\Handler\StreamHandler(APP_LOG, Monolog\Logger::DEBUG));
# $log->addWarning('Foo');

function getCoinList()
{
  $client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'https://www.cryptocompare.com/api/data/',
    // You can set any number of default request options.
    'timeout'  => 2.0,
  ]);
  $response = $client->request('GET', 'coinlist');
  $json= $response->getBody();
  return $json;
}

function getUSDPriceData($str)
{
  $client = new Client([
    // Base URI is used with relative requests
    // 'base_uri' => 'https://min-api.cryptocompare.com/data/',
    // You can set any number of default request options.
    'timeout'  => 2.0,
  ]);
  $response = $client->request('GET', 'https://min-api.cryptocompare.com/data/price?fsym=USD&tsyms=' . $str);
  $json= $response->getBody();
}


