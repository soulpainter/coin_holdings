<?php
namespace CoinHolding;

require_once('bootstrap.php');

use GuzzleHttp\Client;

$bittrexHoldings = array('BTC','DASH','DGB','DGC','EMC2',
                         'EXP','FTP','MAID','MEC','NLG',
                         'PTC','QRL','THC','XMR');

$coinbaseHoldings = array('BTC','ETH','BCH','LTC');

$krakenHoldings = array('XRP','XMR','MLN','XLM','GNO','ETC',
                        'EOS','DOGE','DASH','BCH','BTC','REP');

$jaxxHoldings = array(
  'BTC' => 0.42241173,
  'BCH' => 1.21864032,
  'ETH' => 6.83051838,
  'DASH' => 5,
  'LTC' => 9.37812472,
  'ZEC' => 2.80552706,
  'ETC' => 4.99,
  'DOGE' => 166411.73,
  'EOS' => 155.26323,
  'GNO' => 2.01587,
  'ICN' => 99.8,
  'MLN' => 5.83505,
  'REP' => 22.2352 
);

$cryptopiaHoldings = array('DBG','NOTE','GAME','NVC','UIS','XVG');

$allHoldings = array_merge($bittrexHoldings, $coinbaseHoldings, $krakenHoldings, $jaxxHoldings, $cryptopiaHoldings);
$allHoldings = $jaxxHoldings;

if(file_exists(TEST_COINLIST_FILE))
{
  $json = file_get_contents(TEST_COINLIST_FILE);
}
else 
{
  $json = getCoinList();
}

$coinList = json_decode($json, true);

$priceString = implode(',', array_keys($allHoldings));

if(file_exists(TEST_USD_PRICE_FILE))
{
  $priceJson = file_get_contents(TEST_USD_PRICE_FILE);
}
else
{
  $priceJson = getUSDPriceData($priceString);
}

$usdPrices = json_decode($priceJson, true);

$totalHoldingsValueUSD = 0;

foreach($coinList['Data'] as $symbol=>$coinData)
{
  if(array_key_exists($symbol, $allHoldings))
  {
    $usdValue = array_key_exists($symbol,$usdPrices) ? 1/$usdPrices[$symbol] : 0;
    $totalValue = $usdValue * $allHoldings[$symbol];
    $totalHoldingsValueUSD += $totalValue;
    $totalValue = number_format($totalValue, 2);
    $log->addDebug('Found', ['symbol' => $symbol, 'usd_value' => $usdValue, 'total_coins' => $allHoldings[$symbol], 'total_usd_value' => $totalValue]);
    unset($allHoldings[$symbol]);
  }
}
$log->addDebug('TotalHoldingsValueUSD', ['total_holdings_usd_value' => number_format($totalHoldingsValueUSD, 2)]);

if(count($allHoldings) > 0)
{
  $log->addWarning('NotFound:', $allHoldings);
}

