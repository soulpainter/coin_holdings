<?php
namespace CryptoClient;

use CryptoClient\BittrexHoldings;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

class BittrexClient 
{
  const API_URL = 'https://bittrex.com/api/v1.1/';

  private $apiKey;
  private $apiSecrect;

  private $client;
  private $logger;
  private $cache;

  private $coinBalances = array();

  public function __construct($apiKey, $apiSecret, Client $client, $logger, $cache)
  {
    $this->apiKey = $apiKey;
    $this->apiSecret = $apiSecret;
    $this->client = $client;
    $this->logger = $logger;
    $this->cache = $cache;
  }

  private function generateURI($uri)
  {
    $nonce=time();
    $uri = $uri . '?apikey='.$this->apiKey.'&nonce='.$nonce;
    return $uri;
  }

  private function generateAuthSignature($baseUri)
  {
    $sign=hash_hmac('sha512',$this->generateURI($baseUri),$this->apiSecret);
    return $sign;
  }

  private function getAccountBalances()
  {
    $accountBalances = $this->cache->getCacheFile();
    if($accountBalances)
    {
      return $accountBalances;
    }

    $baseUri = 'https://bittrex.com/api/v1.1/account/getbalances';

    try
    {
      $response = $this->client->request('GET', $this->generateURI($baseUri), [
        'headers' => [
          'apisign' => $this->generateAuthSignature($baseUri),
          'Accept'     => 'application/json',
        ]
      ]);
      $accountBalances = $response->getBody();
      $json = json_decode($accountBalances, true);
      $this->cache->writeFileCache($json);
      return $json;
    
    }
    catch(Exception $e)
    {
      $this->addError('BittrexAPIException', $e);
    }
  }

  private function compactCoins($balances)
  {
    foreach($balances['result'] as $coin)
    {
      if($coin['Balance'] > 0)
      {
        $this->coinBalances[$coin['Currency']] = $coin['Balance'];
      }
    }
  }

  public function getCoinBalances()
  {
    $this->compactCoins($this->getAccountBalances());
    return $this->coinBalances;
  }
}

