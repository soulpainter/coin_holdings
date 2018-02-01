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
use CryptoClient\BinanceClient;
use CryptoClient\CryptoCache;
use CryptoClient\CryptoMachine;

$config = parse_ini_file('../config.ini');

setlocale(LC_MONETARY, 'en_US');

define('APP_NAME', 'CoinHolding');
define('APP_LOG', __DIR__ . '/../storage/logs/app.log');

$slimConfig = [
    'settings' => [
        'displayErrorDetails' => true,

        'logger' => [
            'name' => APP_NAME,
            'level' => Monolog\Logger::DEBUG,
            'path' => APP_LOG
        ],
    ],
];
$app = new \Slim\App($slimConfig);
$container = $app->getContainer();

$logger = new Monolog\Logger(APP_NAME);
$logger->pushHandler(new Monolog\Handler\StreamHandler(APP_LOG, Monolog\Logger::DEBUG));

$container['cryptoMachine'] = $cryptoMachine;
$cryptoMachine = new CryptoMachine($logger);

$client = new Client([
  // You can set any number of default request options.
  'timeout'  => 3.0,
]);

$bittrexCache = new CryptoCache($config['BITTREX_CACHE_FILE'], $config['BITTREX_CACHE_TTL'], $logger);
$bittrex = new BittrexClient($config['BITTREX_API_KEY'], $config['BITTREX_API_SECRECT'], $client, $logger, $bittrexCache);

$krakenCache = new CryptoCache($config['KRAKEN_CACHE_FILE'], $config['KRAKEN_CACHE_TTL'], $logger);
$kraken = new KrakenClient($config['KRAKEN_API_KEY'], $config['KRAKEN_API_SECRET'], $config['KRAKEN_BETA_FLAG'], $logger, $krakenCache);

$coinbaseCache = new CryptoCache($config['COINBASE_CACHE_FILE'], $config['COINBASE_CACHE_TTL'], $logger);
$coinbase = new CoinbaseClient($config['COINBASE_API_KEY'], $config['COINBASE_API_SECRET'], $logger, $coinbaseCache);

$binanceCache = new CryptoCache($config['BINANCE_CACHE_FILE'], $config['BINANCE_CACHE_TTL'], $logger);
$binance = new BinanceClient($config['BINANCE_API_KEY'], $config['BINANCE_API_SECRET'], $logger, $binanceCache);

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

$cryptoCompare = new CryptoCompareClient($client, $logger);

