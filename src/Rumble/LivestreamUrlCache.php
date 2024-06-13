<?php

declare(strict_types=1);

namespace Mati\Rumble;

use Psr\Cache\CacheItemPoolInterface;

final readonly class LivestreamUrlCache
{
  private const string LIVESTREAM_URL_CACHE_KEY = 'mati.livestream_url';

  public function __construct(
    private CacheItemPoolInterface $cache,
  ) {
    // Do nothing.
  }

  public function getCachedLivestreamUrl(): mixed
  {
    return $this->cache->getItem(self::LIVESTREAM_URL_CACHE_KEY)->get();
  }

  public function storeLastFailedLivestreamUrl(string $livestreamUrl): void
  {
    $livestreamUrlCacheItem = $this->cache->getItem(self::LIVESTREAM_URL_CACHE_KEY);
    $livestreamUrlCacheItem->set($livestreamUrl);
    $this->cache->save($livestreamUrlCacheItem);
  }
}
