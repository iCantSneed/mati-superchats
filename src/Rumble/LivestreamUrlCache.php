<?php

declare(strict_types=1);

namespace Mati\Rumble;

use Mati\MatiConfiguration;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class LivestreamUrlCache
{
  public function __construct(
    private CacheItemPoolInterface $cache,
    #[Autowire(MatiConfiguration::PARAM_LIVESTREAM_URL_CACHE_KEY)]
    private string $livestreamUrlCacheKey,
  ) {
    // Do nothing.
  }

  public function getCachedLivestreamUrl(): mixed
  {
    return $this->cache->getItem($this->livestreamUrlCacheKey)->get();
  }

  public function storeLastFailedLivestreamUrl(string $livestreamUrl): void
  {
    $livestreamUrlCacheItem = $this->cache->getItem($this->livestreamUrlCacheKey);
    $livestreamUrlCacheItem->set($livestreamUrl);
    $this->cache->save($livestreamUrlCacheItem);
  }
}
