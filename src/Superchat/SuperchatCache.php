<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Dto\SuperchatsData;
use Mati\Entity\Stream;
use Mati\Entity\Superchat;
use Mati\MatiConfiguration;
use Mati\Repository\SuperchatRepository;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class SuperchatCache
{
  public function __construct(
    private CacheItemPoolInterface $cache,
    private SuperchatRepository $superchatRepository,
    private LoggerInterface $logger,
    #[Autowire(env: MatiConfiguration::ENV_SUPERCHATS_CACHE_KEY)]
    private string $cacheKey,
  ) {
    // Do nothing.
  }

  public function getLatestSuperchats(): SuperchatsData
  {
    $superchatsCacheItem = $this->cache->getItem($this->cacheKey);
    $superchatsData = $superchatsCacheItem->get();
    if ($superchatsData instanceof SuperchatsData) {
      $this->logger->debug('SuperchatCache: cache hit', ['superchatsData' => $superchatsData]);

      return $superchatsData;
    }

    $this->logger->notice('SuperchatCache: cache miss or superchats is not a valid object, refreshing cache');
    $superchats = $this->superchatRepository->findLatest();
    \assert(isset($superchats[0]));
    $prevStreamId = $superchats[0]->getStream()?->getPrev()?->getId();
    \assert(null !== $prevStreamId);

    $superchatsData = new SuperchatsData(superchats: $superchats, prevStreamId: $prevStreamId);
    $superchatsCacheItem->set($superchatsData);
    $this->cache->save($superchatsCacheItem);

    return $superchatsData;
  }

  public function storeSuperchat(Superchat $superchat): void
  {
    $stream = $superchat->getStream();
    \assert(null !== $stream);

    $superchatsCacheItem = $this->cache->getItem($this->cacheKey);
    $superchatsData = $this->getSuperchatsDataFromCache($superchatsCacheItem, $stream);
    $superchatsData->superchats[] = $superchat;
    $superchatsCacheItem->set($superchatsData);
    $this->cache->save($superchatsCacheItem);
  }

  private function getSuperchatsDataFromCache(CacheItemInterface $superchatsCacheItem, Stream $stream): SuperchatsData
  {
    $prevStreamId = $stream->getPrev()?->getId();
    \assert(\is_int($prevStreamId));

    $superchatsData = $superchatsCacheItem->get();
    if (!$superchatsData instanceof SuperchatsData) {
      $this->logger->notice('SuperchatCache: cache miss or superchats is not a valid object, refreshing cache');
      $superchats = $this->superchatRepository->findBy(['stream' => $stream]);

      return new SuperchatsData(superchats: $superchats, prevStreamId: $prevStreamId);
    }

    if ($superchatsData->prevStreamId !== $prevStreamId) {
      $this->logger->notice('SuperchatCache: outdated prevStreamId, clearing cache');

      return new SuperchatsData(superchats: [], prevStreamId: $prevStreamId);
    }

    $this->logger->debug('SuperchatCache: cache hit', ['superchatsData' => $superchatsData]);

    return $superchatsData;
  }
}
