<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Entity\Stream;
use Mati\Entity\Superchat;
use Mati\Repository\SuperchatRepository;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

final readonly class SuperchatCache
{
  private const string SUPERCHATS_CACHE_KEY = 'mati.superchats';

  public function __construct(
    private CacheItemPoolInterface $matiCache,
    private SuperchatRepository $superchatRepository,
    private LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  /**
   * @return non-empty-list<Superchat>
   */
  public function getLatestSuperchats(): array
  {
    $superchatsCacheItem = $this->matiCache->getItem(self::SUPERCHATS_CACHE_KEY);

    if (null !== ($superchats = self::extractSuperchats($superchatsCacheItem))) {
      $this->logger->debug('SuperchatCache: cache hit', ['superchats' => $superchats]);

      return $superchats;
    }

    $this->logger->notice('SuperchatCache: cache miss or superchats is not a valid object, refreshing cache');
    $superchats = $this->superchatRepository->findLatest();
    $superchatsCacheItem->set($superchats);
    $this->matiCache->save($superchatsCacheItem);

    return $superchats;
  }

  public function storeSuperchat(Superchat $superchat): void
  {
    $superchatsCacheItem = $this->matiCache->getItem(self::SUPERCHATS_CACHE_KEY);
    $superchats = $this->getSuperchatsFromCache($superchatsCacheItem, $superchat->getStream());
    $superchats[] = $superchat;
    $superchatsCacheItem->set($superchats);
    $this->matiCache->save($superchatsCacheItem);
  }

  /**
   * @return list<Superchat>
   */
  private function getSuperchatsFromCache(CacheItemInterface $superchatsCacheItem, Stream $stream): array
  {
    $prevStreamId = $stream->getPrev()?->getId();
    \assert(\is_int($prevStreamId));

    if (null === ($superchats = self::extractSuperchats($superchatsCacheItem))) {
      $this->logger->notice('SuperchatCache: cache miss or superchats is not a non-empty list, refreshing cache');
      $superchats = $this->superchatRepository->findBy(['stream' => $stream]);
      \assert(array_is_list($superchats));

      return $superchats;
    }

    if ($superchats[0]->getStream()->getPrev()?->getId() !== $prevStreamId) {
      $this->logger->notice('SuperchatCache: no superchats or outdated prevStreamId, clearing cache');

      return [];
    }

    $this->logger->debug('SuperchatCache: cache hit', ['superchats' => $superchats]);

    return $superchats;
  }

  /**
   * @return ?non-empty-list<Superchat>
   */
  private static function extractSuperchats(CacheItemInterface $superchatsCacheItem): ?array
  {
    /** @psalm-suppress MixedAssignment */ $superchats = $superchatsCacheItem->get();
    if (!\is_array($superchats) || empty($superchats) || !array_is_list($superchats)) {
      return null;
    }

    self::validateSuperchatsArray($superchats);

    return $superchats;
  }

  /**
   * @psalm-assert non-empty-list<Superchat> $superchats
   */
  private static function validateSuperchatsArray(array $superchats): void
  {
    foreach ($superchats as $superchat) {
      \assert($superchat instanceof Superchat);
    }
  }
}
