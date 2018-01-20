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

  private $coinBalances = array();

  public function __construct($apiKey, $apiSecret, Client $client, $logger)
  {
    $this->apiKey = $apiKey;
    $this->apiSecret = $apiSecret;
    $this->client = $client;
    $this->logger = $logger;
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
    $cacheFile = 'storage/data/bittrex_balances.json';

    if(file_exists($cacheFile))
    {
      $json = file_get_contents($cacheFile);
      return json_decode($json, true);
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
    }
    catch(Exception $e)
    {
      $this->addError('BittrexAPIException', $e);
    }

    return json_decode($response->getBody(), true);
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

#$bittrex = new BittrexClient($config['BITTREX_API_KEY'], $config['BITTREX_API_SECRECT'], new Client());
#print_r($bittrex->getCoinBalances());








