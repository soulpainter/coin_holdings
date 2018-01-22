<?php
namespace CryptoClient;

class CryptoMachine
{

  private $logger;

  private $coinHoldingMap = [];

  public function __construct($logger)
  {
    $this->logger = $logger;
  }

  public function createCoinHoldingMap($holdings)
  {
    # create an array of symbol => amount in all exchanges / wallets
    foreach($holdings as $exchange=>$coins)
    {
      $this->logger->addDebug($exchange, $coins);
      foreach($coins as $symbol=>$amount)
      {
        $this->logger->addDebug($symbol, [$amount]);
        if(isset($this->coinHoldingMap[$symbol]))
        {
          $this->logger->addDebug('FoundSymbolInCoinHoldingMap', [$symbol, $amount]);
          $this->coinHoldingMap[$symbol] += $amount;
        }
        else
        {
          $this->logger->addDebug('NotFoundSymbolInCoinHoldingMap', [$symbol, $amount]);
          $this->coinHoldingMap[$symbol] = $amount;
        }
      }
    }
    $this->logger->addDebug('FullCoinHoldingMapOfCoins', $this->coinHoldingMap);
  }

  public function computeCoinTotals($coinValueMap, $usdPrices)
  {
    $returnData = [];
    $returnData['coins'] = [];
    $returnData['totalHoldingsValueUSD'] = 0;
    $returnData['coinHoldingMapNotFound'] = $this->coinHoldingMap;

    foreach($coinValueMap as $symbol=>$coinData)
    {
      if(array_key_exists($symbol, $this->coinHoldingMap))
      {
        $usdValue = array_key_exists($symbol,$usdPrices) ? 1/$usdPrices[$symbol] : 0;
        $totalValue = $usdValue * $this->coinHoldingMap[$symbol];
        $returnData['totalHoldingsValueUSD'] += $totalValue;
        $totalValue = number_format($totalValue, 2);

        $result = ['symbol' => $symbol, 'usd_value' => $usdValue, 'total_coins' => $this->coinHoldingMap[$symbol], 'total_usd_value' => $totalValue];
        $this->logger->addDebug('Found', $result);
        $returnData['coins'][$symbol] = $result;
        unset($returnData['coinHoldingMapTemp'][$symbol]);
      }
    }
    return $returnData;
  }

  public function getCoinHoldingMap()
  {
    return $this->removeZeroBalances();
  }

  private function removeZeroBalances()
  {
    return array_filter($this->coinHoldingMap, function($val) { return $val > 0; });
  }
}

