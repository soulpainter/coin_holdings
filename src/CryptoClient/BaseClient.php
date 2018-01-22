<?php
namespace CryptoClient;

class BaseClient
{
  const HOLDING_FILE = __DIR__ . '/../../storage/data/binance_balances.log';
  const API_CALL_SECONDS = 3600; // cache results for an hour

  private $apiKey;
  private $apiSecrect;
  private $logger;

  private $coinBalances = array();

  public function __construct($apiKey, $apiSecret, $logger)
  {
    $this->apiKey = $apiKey;
    $this->apiSecret = $apiSecret;
    $this->logger = $logger;
  }

  public function getCoinBalances()
  {
    return $this->coinBalances;
  }

  private function getAccountBalances()
  {
    if($contents = $this->hasFileCache(self::HOLDING_FILE))
    {
      return unserialize($contents);
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

