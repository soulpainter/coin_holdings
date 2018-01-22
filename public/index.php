<?php

require_once('bootstrap.php');

$allHoldings = array();

//TODO: YOYO and IOTA are having issues
$allHoldings['binance'] = $binance->getCoinBalances();

$allHoldings['bittrex'] = $bittrex->getCoinBalances();

$allHoldings['coinbase'] = $coinbase->getCoinBalances();

$allHoldings['kraken'] = $kraken->getCoinBalances();

$allHoldings['jaxx'] = array(
  'BTC' => 0.99544173,
  'BCH' => 1.21864032,
  'ETH' => 6.83051838,
  'DASH' => 8.53602,
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

# pull in the coin list from crypto compare
$json = $cryptoCompare->getCoinListJson();
$coinList = json_decode($json, true);

# create an array of symbol => amount in all exchanges / wallets
$coinHoldingMap = array();
foreach($allHoldings as $exchange=>$coins)
{
  $logger->addDebug($exchange, $coins);
  foreach($coins as $symbol=>$amount)
  {
    $logger->addDebug($symbol, [$amount]);
    if(isset($coinHoldingMap[$symbol]))
    {
      $logger->addDebug('FoundSymbolInCoinHoldingMap', [$symbol, $amount]);
      $coinHoldingMap[$symbol] += $amount;
    }
    else
    {
      $logger->addDebug('NotFoundSymbolInCoinHoldingMap', [$symbol, $amount]);
      $coinHoldingMap[$symbol] = $amount;
    }
  }
}
$logger->addDebug('FullCoinHoldingMapOfCoins', $coinHoldingMap);

$priceString = implode(',', array_keys($coinHoldingMap));
$logger->addDebug('CryptoCompare:getUSDPriceData:priceString', [$priceString]);
$priceJson = $cryptoCompare->getUSDPriceData($priceString);
$usdPrices = json_decode($priceJson, true);

$totalHoldingsValueUSD = 0;

foreach($coinList['Data'] as $symbol=>$coinData)
{
  if(array_key_exists($symbol, $coinHoldingMap))
  {
    $usdValue = array_key_exists($symbol,$usdPrices) ? 1/$usdPrices[$symbol] : 0;
    $totalValue = $usdValue * $coinHoldingMap[$symbol];
    $totalHoldingsValueUSD += $totalValue;
    $totalValue = number_format($totalValue, 2);
    $logger->addDebug('Found', ['symbol' => $symbol, 'usd_value' => $usdValue, 'total_coins' => $coinHoldingMap[$symbol], 'total_usd_value' => $totalValue]);
    unset($coinHoldingMap[$symbol]);
  }
}

print "Total Holdings Value in USD: $" . number_format($totalHoldingsValueUSD, 2) . "\n";
$logger->addDebug('TotalHoldingsValueUSD', ['total_holdings_usd_value' => number_format($totalHoldingsValueUSD, 2)]);

if(count($coinHoldingMap) > 0)
{
  $logger->addWarning('NotFound:', $coinHoldingMap);
}

