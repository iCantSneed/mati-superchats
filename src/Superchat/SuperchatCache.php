<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Dto\SuperchatsData;
use Mati\Entity\Stream;
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
    $superchatsData = $this->getSuperchatsDataFromCache($superchatsCacheItem, $superchat->getStream());
    $superchatsData->superchats[] = $superchat;
    $superchatsCacheItem->set($superchatsData);
    $this->cache->save($superchatsCacheItem);
  }

  private function getSuperchatsDataFromCache(CacheItemInterface $superchatsCacheItem, ?Stream $stream): SuperchatsData
  {
    $prevStreamId = $stream?->getPrev()?->getId();
    \assert(\is_int($prevStreamId));

    $superchatsData = $superchatsCacheItem->get();
    if (!$superchatsData instanceof SuperchatsData) {
      $this->logger->notice('SuperchatCache: cache miss or superchats is not a valid object, clearing cache');

      return self::newSuperchatsData($prevStreamId);
    }

    if ($superchatsData->prevStreamId !== $prevStreamId) {
      $this->logger->notice('SuperchatCache: outdated prevStreamId, clearing cache');

      return self::newSuperchatsData($prevStreamId);
    }

    $this->logger->debug('SuperchatCache: cache hit', ['superchatsData' => $superchatsData]);

    return $superchatsData;
  }

  private static function newSuperchatsData(int $prevStreamId): SuperchatsData
  {
    return new SuperchatsData(superchats: [], prevStreamId: $prevStreamId);
  }
}
