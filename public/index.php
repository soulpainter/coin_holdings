<?php

require_once('bootstrap.php');

$allHoldings = array();

//TODO: YOYO and IOTA are having issues
$allHoldings['binance'] = $binance->getCoinBalances();

//TODO: FIX - API CALL SEEMS TO BE FAILING NOW - WTF???
#$allHoldings['bittrex'] = $bittrex->getCoinBalances();

$allHoldings['coinbase'] = $coinbase->getCoinBalances();

$allHoldings['kraken'] = $kraken->getCoinBalances();

$allHoldings['jaxx'] = array(
  'BTC' => 0.85213173,
  'BCH' => 1.21864032,
  'ETH' => 6.83051838,
  'DASH' => 5,
  'LTC' => 9.37812472,
  'ZEC' => 2.80552706,
  'ETC' => 39.05237,
  'DOGE' => 166411.73,
  'EOS' => 155.26323,
  'GNO' => 2.01587,
  'ICN' => 99.8,
  'MLN' => 5.83505,
  'REP' => 22.2352 
);

#$cryptopiaHoldings = array('DBG','NOTE','GAME','NVC','UIS','XVG');
#$allHoldings['cryptopia'] = $cryptopia->getCoinBalances();

$sums = array();
foreach($allHoldings as $exchange=>$coins)
{
  $logger->addDebug($exchange, $coins);
  foreach($coins as $symbol=>$amount)
  {
    $logger->addDebug($symbol, [$amount]);
    if(isset($sums[$symbol]))
    {
      $logger->addDebug('FoundSymbolInSums', [$symbol, $amount]);
      $sums[$symbol] += $amount;
    }
    else
    {
      $logger->addDebug('NotFoundSymbolInSums', [$symbol, $amount]);
      $sums[$symbol] = $amount;
    }
  }
}
$logger->addDebug('FullSumOfCoins', $sums);

$allHoldings = $sums;

$json = $cryptoCompare->getCoinListJson();
$coinList = json_decode($json, true);

$priceString = implode(',', array_keys($allHoldings));
#print $priceString;
#exit;

$priceJson = $cryptoCompare->getUSDPriceData($priceString);
$usdPrices = json_decode($priceJson, true);

$totalHoldingsValueUSD = 0;

#print_r($allHoldings);
#exit;

#print_r($coinList['Data']);

foreach($coinList['Data'] as $symbol=>$coinData)
{
  #print "$symbol\n";
  if(array_key_exists($symbol, $allHoldings))
  {
    $usdValue = array_key_exists($symbol,$usdPrices) ? 1/$usdPrices[$symbol] : 0;
    $totalValue = $usdValue * $allHoldings[$symbol];
    $totalHoldingsValueUSD += $totalValue;
    $totalValue = number_format($totalValue, 2);
    $logger->addDebug('Found', ['symbol' => $symbol, 'usd_value' => $usdValue, 'total_coins' => $allHoldings[$symbol], 'total_usd_value' => $totalValue]);
    unset($allHoldings[$symbol]);
  }
}

print "Total Holdings Value in USD: $" . number_format($totalHoldingsValueUSD, 2) . "\n";
$logger->addDebug('TotalHoldingsValueUSD', ['total_holdings_usd_value' => number_format($totalHoldingsValueUSD, 2)]);

if(count($allHoldings) > 0)
{
  $logger->addWarning('NotFound:', $allHoldings);
}

