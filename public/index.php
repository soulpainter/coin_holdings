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

// you can calculate total of all accounts with this coinHoldingMap
#$cryptoMachine->createCoinHoldingMap($allHoldings);

// or you can do a single exchange or wallet
$cryptoMachine->createCoinHoldingMap(array($allHoldings['jaxx']));

// now we have the map to do the calculations on
$coinHoldingMap = $cryptoMachine->getCoinHoldingMap();

$priceString = implode(',', array_keys($coinHoldingMap));
$logger->addDebug('CryptoCompare:getUSDPriceData:priceString', [$priceString]);
$priceJson = $cryptoCompare->getUSDPriceData($priceString);
$usdPrices = json_decode($priceJson, true);

$totalHoldingInfo = $cryptoMachine->computeCoinTotals($coinList['Data'], $usdPrices);

print "Total Holdings Value in USD: $" . number_format($totalHoldingInfo['totalHoldingsValueUSD'], 2) . "\n";
$logger->addDebug('TotalHoldingsValueUSD', ['total_holdings_usd_value' => number_format($totalHoldingInfo['totalHoldingsValueUSD'], 2)]);

if(count($totalHoldingInfo['coinHoldingMapTemp']) > 0)
{
  $logger->addWarning('NotFound:', $totalHoldingInfo['coinHoldingMapTemp']);
}

