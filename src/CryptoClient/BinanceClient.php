<?php
namespace CryptoClient;

use Binance\API;

class BinanceClient
{
  const HOLDING_FILE = __DIR__ . '/../../storage/data/binance_balances.log';
  const API_CALL_SECONDS = 3600; // cache results for an hour

  private $binanceAPI;

  private $apiKey;
  private $apiSecrect;
  private $logger;

  private $coinBalances = array();

  public function __construct($apiKey, $apiSecret, $logger)
  {
    $this->apiKey = $apiKey;
    $this->apiSecret = $apiSecret;
    $this->logger = $logger;

    $this->binanceAPI = new \Binance\API($apiKey, $apiSecret);
  }

  private function getAccountBalances()
  {
    if($contents = $this->hasFileCache(self::HOLDING_FILE))
    {
      return unserialize($contents);
    }

    try
    {
      $balances = $this->binanceAPI->balances('BTC');
      $this->writeFileCache(self::HOLDING_FILE, serialize($balances));
      return $balances;
    }
    catch(Exception $e)
    {
      $this->addError('BinanceClientException:getAccountBalances', $e);
    }
  }

  private function hasFileCache($cacheFile)
  {
    if(file_exists($cacheFile))
    {
      if(time()-filemtime($cacheFile) < 1 * self::API_CALL_SECONDS)
      {
        $contents = file_get_contents($cacheFile);
        return $contents;
      }
    }
  }

  private function writeFileCache($cacheFile, $contents)
  {
    file_put_contents($cacheFile, $contents);
  }

  public function getCoinBalances()
  {
    $res = $this->getAccountBalances();

    $binanceHoldings = array();
    foreach($res as $symbol=>$coin)
    {
      $binanceHoldings[$symbol] = $coin['available'];
    }

    $this->compactCoins($binanceHoldings);
    return $this->coinBalances;
  }

  private function compactCoins($balances)
  {
    foreach($balances as $symbol => $value)
    {
      if($value > 0)
      {
        $this->coinBalances[$symbol] = $value;
      }
    }
  }
}

