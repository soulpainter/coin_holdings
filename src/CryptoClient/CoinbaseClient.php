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
  private $cache;

  private $coinBalances = array();

  public function __construct($apiKey, $apiSecret, $logger, $cache)
  {
    $this->apiKey = $apiKey;
    $this->apiSecret = $apiSecret;
    $this->logger = $logger;
    $this->cache = $cache;

    $configuration = Configuration::apiKey($apiKey, $apiSecret);
    $this->coinbaseAPI = Client::create($configuration);
  }

  private function getAccountBalances()
  {
    if($accountBalances = $this->cache->getCacheFile())
    {
      return $accountBalances;
    }

    try
    {
      $accounts = $this->coinbaseAPI->getAccounts();
      $this->cache->writeFileCache($accounts);
      return $accounts;
    }
    catch(Exception $e)
    {
      $this->addError('CoinbaseClientException:getAccountBalances', $e);
    }
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

