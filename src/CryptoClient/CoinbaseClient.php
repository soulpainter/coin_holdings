<?php
namespace CryptoClient;

use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;

class CoinbaseClient
{
  const HOLDING_FILE = __DIR__ . '/../../storage/data/coinbase_balances.log';
  const API_CALL_SECONDS = 3600; // cache results for an hour

  private $coinbaseAPI;

  private $apiKey;
  private $apiSecrect;
  private $logger;

  private $coinBalances = array();

  public function __construct($apiKey, $apiSecret, $logger)
  {
    $this->apiKey = $apiKey;
    $this->apiSecret = $apiSecret;
    $this->logger = $logger;

    $configuration = Configuration::apiKey($apiKey, $apiSecret);
    $this->coinbaseAPI = Client::create($configuration);
  }

  private function getAccountBalances()
  {
    if($contents = $this->hasFileCache(self::HOLDING_FILE))
    {
      return unserialize($contents);
    }

    try
    {
      $accounts = $this->coinbaseAPI->getAccounts();
      $this->writeFileCache(self::HOLDING_FILE, serialize($accounts));
      return $accounts;
    }
    catch(Exception $e)
    {
      $this->addError('CoinbaseClientException:getAccountBalances', $e);
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

    $coinbaseHoldings = array();
    foreach($res as $key=>$value)
    {
      $coinbaseHoldings[$value->getBalance()->getCurrency()] = $value->getBalance()->getAmount();
    }
    return $coinbaseHoldings;
  }
}

