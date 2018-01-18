<?php
namespace CoinHolding;

require_once('bootstrap.php');

use GuzzleHttp\Client;

define('TEST_COINLIST_FILE', '/Users/colonel32/Documents/coin_holdings/storage/data/coinlist.json');

$bittrexHoldings = array('BTC','DASH','DGB','DGC','EMC2',
                 'EXP','FTP','MAID','MEC','NLG',
                 'PTC','QRL','THC','XMR');

$coinbaseHoldings = array('BTC','ETH','BCH','LTC');

$krakenHoldings = array('XRP','XMR','MLN','XLM','GNO','ETC','EOS','DOGE','DASH','BCH','BTC','REP');

$jaxxHoldings = array();

$cryptopiaHoldings = array('DBG','NOTE','GAME','NVC','UIS','XVG');

$allHoldings = array_values(array_unique(array_merge($bittrexHoldings, $coinbaseHoldings, $krakenHoldings, $jaxxHoldings, $cryptopiaHoldings)));

if(file_exists(TEST_COINLIST_FILE))
{
  $json = file_get_contents(TEST_COINLIST_FILE);
}
else 
{
  $client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'https://www.cryptocompare.com/api/data/',
    // You can set any number of default request options.
    'timeout'  => 2.0,
  ]);
  $response = $client->request('GET', 'coinlist');
  $json= $response->getBody();
}

$coinList = json_decode($json, true);
#print_r($coinList);
#print_r($allHoldings);
#exit;

print implode(',', $allHoldings);
exit;

foreach($coinList['Data'] as $symbol=>$coinData)
{
  if(in_array($symbol, $allHoldings))
  {
    print "Found $symbol\n";
    if (($key = array_search($symbol, $allHoldings)) !== false) {
      unset($allHoldings[$key]);
    }
  }
}

if(count($allHoldings) > 0)
{
  $log->addWarning('Coin Symbols Not Found:', $allHoldings);
}





