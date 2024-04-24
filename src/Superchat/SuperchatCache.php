<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Dto\SuperchatsData;
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
    $superchatsData = $this->getSuperchatsDataFromCache($superchatsCacheItem);
    $superchatsData->superchats[] = $superchat;
    $superchatsCacheItem->set($superchatsData);
    $this->cache->save($superchatsCacheItem);
  }

  private function getSuperchatsDataFromCache(CacheItemInterface $superchatsCacheItem): SuperchatsData
  {
    $superchatsData = $superchatsCacheItem->get();
    if (!$superchatsData instanceof SuperchatsData) {
      $this->logger->notice('SuperchatCache: cache miss or superchats is not a valid object, clearing cache');

      return new SuperchatsData([]);
    }

    if (empty($superchatsData->superchats)) {
      $this->logger->info('SuperchatCache: superchats array is empty');

      return $superchatsData;
    }

    $oldestSuperchat = reset($superchatsData->superchats);
    $oldestSuperchatCreatedInterval = $oldestSuperchat->getCreated()?->diff(new \DateTimeImmutable(), true);
    if (null === $oldestSuperchatCreatedInterval || $oldestSuperchatCreatedInterval->days >= 1) {
      $this->logger->info('SuperchatCache: superchats cache is stale, clearing cache');

      return new SuperchatsData([]);
    }

    $this->logger->debug('SuperchatCache: cache hit', ['superchatsData' => $superchatsData]);

    return $superchatsData;
  }
}
