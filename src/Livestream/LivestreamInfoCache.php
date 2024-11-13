<?php

declare(strict_types=1);

namespace Mati\Livestream;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final readonly class LivestreamInfoCache
{
  private const string LIVESTREAM_CACHE_KEY = 'mati.livestream';

  private CacheItemInterface $cacheItem;

  public function __construct(
    private CacheItemPoolInterface $cache,
  ) {
    $this->cacheItem = $cache->getItem(self::LIVESTREAM_CACHE_KEY);
  }

  public function getLivestreamInfo(): ?LivestreamInfo
  {
    $livestreamInfo = $this->cacheItem->get();
    \assert(null === $livestreamInfo || $livestreamInfo instanceof LivestreamInfo);

    return $livestreamInfo;
  }

  public function setLivestreamInfo(?LivestreamInfo $livestreamInfo): void
  {
    $this->cacheItem->set($livestreamInfo);
    $this->cache->save($this->cacheItem);
  }
}
