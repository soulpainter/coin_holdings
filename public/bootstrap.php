<?php
error_reporting(E_ALL & ~E_NOTICE);

require __DIR__ . '/../vendor/autoload.php';
require_once(__DIR__ . '/../vendor/KittyCatTech/cryptopia-api-php/cryptopiaAPI.php');

use GuzzleHttp\Client;
use CryptoClient\BittrexClient;
use CryptoClient\BittrexHoldings;
use CryptoClient\CryptopiaClient;
use CryptoClient\CryptoCompareClient;
use CryptoClient\KrakenClient;
use CryptoClient\CoinbaseClient;

$config = parse_ini_file('../config.ini');

setlocale(LC_MONETARY, 'en_US');

define('APP_NAME', 'CoinHolding');
define('APP_LOG', '/Users/colonel32/Documents/coin_holdings/storage/logs/app.log');

$log = new Monolog\Logger(APP_NAME);
$log->pushHandler(new Monolog\Handler\StreamHandler(APP_LOG, Monolog\Logger::WARNING));

$client = new Client([
  // You can set any number of default request options.
  'timeout'  => 2.0,
]);

$bittrex = new BittrexClient($config['BITTREX_API_KEY'], $config['BITTREX_API_SECRECT'], $client, $log);

$kraken = new KrakenClient($config['KRAKEN_API_KEY'], $config['KRAKEN_API_SECRET'], $config['KRAKEN_BETA_FLAG'], $log);

$coinbase = new CoinbaseClient($config['COINBASE_API_KEY'], $config['COINBASE_API_SECRET'], $log);

#$cryptopia = new CryptopiaClient($config['CRYPTOPIA_API_KEY'], $config['CRYPTOPIA_API_SECRET'], $client);
/*
try 
{
   // create a new instance of the API Wrapper
   $ct = New Cryptopia('XfkCWKPzBoE5x8pvEmP0SWVRQogZfvvENA84TIKC6CU=', '0524d9fae1a84364a3beb449d128749c');
   print_r($ct->getBalance());
}
catch(Exception $e)
{
  var_dump($e);
}
*/

$cryptoCompare = new CryptoCompareClient($client, $log);

