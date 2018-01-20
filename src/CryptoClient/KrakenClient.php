<?php
namespace CryptoClient;

require_once(__DIR__ . '/../../vendor/krakenfx/kraken-api-client/php/KrakenAPIClient.php');

class KrakenClient
{
  const HOLDING_FILE = __DIR__ . '/../../storage/data/kraken_balances.json';
  const API_CALL_SECONDS = 3600; // cache results for an hour

  private $paywardKrakenAPI;

  private $apiKey;
  private $apiSecrect;
  private $betaFlag = false;

  private $coinBalances = array();

  private $krakenSymbolsToDefaultSymbols = array(
    'XXBT' => 'BTC',
    'XXRP' => 'XRP',
    'XLTC' => 'LTC',
    'XXDG' => 'DOGE',
    'XXLM' => 'XLM',
    'XETH' => 'ETH',
    'XETC' => 'ETC',
    'XREP' => 'REP',
    'XZEC' => 'ZEC',
    'XICN' => 'ICN',
    'XXMR' => 'XMR',
    'XMLN' => 'MLN',
  );

  public function __construct($apiKey, $apiSecret, $betaFlag)
  {
    $this->apiKey = $apiKey;
    $this->apiSecret = $apiSecret;
    $this->betaFlag = $betaFlag;

    $url = $betaFlag ? 'https://api.beta.kraken.com' : 'https://api.kraken.com';
    $sslverify = $betaFlag ? false : true;
    $version = 0;
    $this->paywardKrakenAPI = new \Payward\KrakenAPI($apiKey, $apiSecret, $url, $version, $sslverify);

  }

  private function getAccountBalances()
  {
    if($json = $this->hasFileCache(self::HOLDING_FILE))
    {
      return json_decode($json, true);
    }

    $res = $this->paywardKrakenAPI->QueryPrivate('Balance');
    $this->writeFileCache(self::HOLDING_FILE, json_encode($res));
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

  public function getCoinBalances()
  {
    $res = $this->getAccountBalances();

    $krakenHoldings = array();
    foreach($res['result'] as $key=>$value)
    {
      if(isset($this->krakenSymbolsToDefaultSymbols[$key]))
      {
        $krakenKey = $this->krakenSymbolsToDefaultSymbols[$key];
      }
      else
      {
        $krakenKey = $key;
      }
      $krakenHoldings[$krakenKey] = $value;
    }
    return $krakenHoldings;
  }
}

