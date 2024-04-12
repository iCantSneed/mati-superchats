<?php

declare(strict_types=1);

namespace Mati\Rumble;

use Mati\MatiConfiguration;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use SimplePie\SimplePie;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class RssLivestreamUrlFetcher
{
  private SimplePie $simplepie;

  public function __construct(
    private LivestreamUrlCache $livestreamUrlCache,
    CacheItemPoolInterface $cache,
    private LoggerInterface $logger,
    #[Autowire(MatiConfiguration::PARAM_LIVESTREAM_RSS_URL)]
    string $livestreamRssUrl,
  ) {
    $this->simplepie = new SimplePie();

    $this->simplepie->set_feed_url($livestreamRssUrl);
    $this->simplepie->set_useragent('curl/7.88');
    $this->simplepie->set_cache(new Psr16Cache($cache));
  }

  public function fetchLivestreamUrl(): ?string
  {
    $result = $this->simplepie->init();
    if (!$result) {
      $this->logger->error('RssLivestreamUrlFetcher: simplepie init failure', ['error' => $this->simplepie->error()]);

      return null;
    }

    $cached = false === $this->simplepie->get_raw_data();
    if ($cached) {
      $this->logger->notice('RssLivestreamUrlFetcher: RSS feed not updated since last cached');

      return null;
    }

    $item = $this->simplepie->get_item(0);
    if (null === $item) {
      $this->logger->error('RssLivestreamUrlFetcher: RSS feed is empty');

      return null;
    }

    $livestreamUrl = $item->get_permalink();
    if (null === $livestreamUrl) {
      $this->logger->error('RssLivestreamUrlFetcher: RSS item has no permalink');

      return null;
    }

    $this->logger->debug('RssLivestreamUrlFetcher: got livestream URL', ['livestreamUrl' => $livestreamUrl]);

    if ($this->livestreamUrlCache->getCachedLivestreamUrl() === $livestreamUrl) {
      $this->logger->notice('RssLivestreamUrlFetcher: livestream URL matches cached URL');

      return null;
    }

    return $livestreamUrl;
  }
}
