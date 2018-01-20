<?php
namespace CryptoClient;

use GuzzleHttp\Client;

class CryptopiaClient
{
  const API_URL = 'https://www.cryptopia.co.nz/api/';

  private $apiKey;
  private $apiSecrect;

  private $client;

  private $coinBalances = array();

  public function __construct($apiKey, $apiSecret, Client $client)
  {
    $this->apiKey = $apiKey;
    $this->apiSecret = $apiSecret;
    $this->client = $client;
  }

  private function generateAuthSignature($baseUri, $req = array())
  {
    $nonce = explode(' ', microtime())[1];
    $post_data = json_encode( $req );
    $m = md5( $post_data, true );
    $requestContentBase64String = base64_encode( $m );
    $signature = $this->apiKey . "POST" . strtolower( urlencode( $baseUri ) ) . $nonce . $requestContentBase64String;
    $hmacsignature = base64_encode( hash_hmac("sha256", $signature, base64_decode( $this->apiKey ), true ) );
    $header_value = "amx " . $this->apiKey . ":" . $hmacsignature . ":" . $nonce;
    $header_value;
  }

  private function getAccountBalances()
  {
    $cacheFile = 'storage/data/cryptopia_balances.json';

    if(file_exists($cacheFile))
    {
      $json = file_get_contents($cacheFile);
      return json_decode($json, true);
    }

    $baseUri = 'https://www.cryptopia.co.nz/api/GetBalance';

    $response = $this->client->request('POST', $baseUri, [
      'headers' => [
        'Authorization' => $this->generateAuthSignature($baseUri),
        'Accept'     => 'application/json',
      ]
    ]);

    #var_dump($response->getBody());
    #exit;

    return json_decode($response->getBody(), true);
  }

  private function compactCoins($balances)
  {
    foreach($balances['Data'] as $coin)
    {
      if($coin['Total'] > 0)
      {
        $this->coinBalances[$coin['Symbol']] = $coin['Total'];
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








