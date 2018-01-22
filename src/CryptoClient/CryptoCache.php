<?php
namespace CryptoClient;

class CryptoCache
{
  const CACHE_DIR = __DIR__ . "/../../storage/data/";

  private $cacheFile;
  private $cacheTTL;
  private $logger;

  public function __construct($cacheFile, $cacheTTL, $logger)
  {
    $this->cacheFile = self::CACHE_DIR . $cacheFile;
    $this->cacheTTL = $cacheTTL;
    $this->logger = $logger;
  }

  public function getCacheFile()
  {
    if($this->hasValidCacheFile())
    {
      $contents = file_get_contents($this->cacheFile);
      return unserialize($contents);
    }
  }

  public function hasValidCacheFile()
  {
    if(file_exists($this->cacheFile) && (time()-filemtime($this->cacheFile) < 1 * $this->cacheTTL))
    {
      return true;
    }
    return false;
  }

  public function writeFileCache($contents)
  {
    file_put_contents($this->cacheFile, serialize($contents));
  }
}

