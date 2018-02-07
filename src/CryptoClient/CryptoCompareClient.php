<?php
namespace CryptoClient;

use GuzzleHttp\Client;
use Monolog\Logger;

class CryptoCompareClient
{

  const COINLIST_FILE = __DIR__ . '/../../storage/data/coinlist.json';
  const USD_PRICE_FILE = __DIR__ . '/../../storage/data/USD_price.json';
  const API_CALL_SECONDS = 3600; // cache results for an hour

  private $client;
  private $logger;

  public function __construct(Client $client, Logger $logger)
  {
    $this->client = $client;
    $this->logger = $logger;
  }

  public function getCoinListData()
  {
    $coinList = $this->getCoinListJson();
    return $coinList['Data'];
  }

  public function getCoinListJson()
  {
    if($json = $this->hasFileCache(self::COINLIST_FILE))
    {
      return $json;
    }

    try
    {
      $response = $this->client->request('GET', 'https://www.cryptocompare.com/api/data/coinlist');
      $json= $response->getBody();

      $this->writeFileCache(self::COINLIST_FILE, $json);

      return $json;
    }
    catch(Exception $e)
    {
      $this->addError('CryptoCompareClientException:getCoinListJson', $e);
    }

  }

  public function getUSDPriceData($str)
  {
    if($json = $this->hasFileCache(self::USD_PRICE_FILE))
    {
      return $json;
    }

    try
    {
      $response = $this->client->request('GET', 'https://min-api.cryptocompare.com/data/price?fsym=USD&tsyms=' . $str);
      $json= $response->getBody();

      $this->writeFileCache(self::USD_PRICE_FILE, $json);

      return $json;
    }
    catch(Exception $e)
    {
      $this->addError('CryptoCompareClientException:getUSDPriceData', $e, array($str));
    }
  }

  private function hasFileCache($cacheFile)
  {
    if(file_exists($cacheFile))
    {
      if(time()-filemtime($cacheFile) < 1 * self::API_CALL_SECONDS)
      {
        $json = file_get_contents($cacheFile);
        return $json;
      }
    }
  }

  private function writeFileCache($cacheFile, $contents)
  {
    file_put_contents($cacheFile, $contents);
  }

}

