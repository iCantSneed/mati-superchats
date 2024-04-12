<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Entity\Superchat;
use Mati\MatiConfiguration;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class SuperchatCache
{
  public function __construct(
    private CacheItemPoolInterface $cache,
    private LoggerInterface $logger,
    #[Autowire(MatiConfiguration::PARAM_SUPERCHATS_CACHE_KEY)]
    private string $cacheKey,
  ) {
    // Do nothing.
  }

  public function storeSuperchat(Superchat $superchat): void
  {
    $superchatsCacheItem = $this->cache->getItem($this->cacheKey);
    $superchats = $this->getSuperchatsFromCache($superchatsCacheItem);
    $superchats[] = $superchat;
    $superchatsCacheItem->set($superchats);
    $this->cache->save($superchatsCacheItem);
  }

  private function getSuperchatsFromCache(CacheItemInterface $superchatsCacheItem): array
  {
    $superchats = $superchatsCacheItem->get();
    if (!\is_array($superchats)) {
      $this->logger->notice('SuperchatCache: cache miss or superchats is not an array, clearing cache');

      return [];
    }

    if (empty($superchats)) {
      $this->logger->info('SuperchatCache: superchats array is empty');

      return [];
    }

    $oldestSuperchat = reset($superchats);
    if (!$oldestSuperchat instanceof Superchat) {
      $this->logger->error('SuperchatCache: superchats array has non-superchat elements, clearing cache');

      return [];
    }

    $oldestSuperchatCreatedInterval = $oldestSuperchat->getCreated()?->diff(new \DateTimeImmutable(), true);
    if (null === $oldestSuperchatCreatedInterval || $oldestSuperchatCreatedInterval->days >= 1) {
      $this->logger->info('SuperchatCache: superchats cache is stale, clearing cache');

      return [];
    }

    $this->logger->debug('SuperchatCache: cache hit', ['superchats' => $superchats]);

    return $superchats;
  }
}
