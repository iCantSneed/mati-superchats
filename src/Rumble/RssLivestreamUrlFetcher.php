<?php

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
    CacheItemPoolInterface $cache,
    private LoggerInterface $logger,
    #[Autowire(MatiConfiguration::PARAM_LIVESTREAM_RSS_URL)] string $livestreamRssUrl,
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

    $cached = $this->simplepie->get_raw_data() === false;
    if ($cached) {
      $this->logger->notice('RssLivestreamUrlFetcher: RSS feed not updated since last cached');
      return null;
    }

    $item = $this->simplepie->get_item(0);
    if ($item === null) {
      $this->logger->error('RssLivestreamUrlFetcher: RSS feed is empty');
      return null;
    }

    $livestreamUrl = $item->get_permalink();
    if ($livestreamUrl === null) {
      $this->logger->error('RssLivestreamUrlFetcher: RSS item has no permalink');
      return null;
    }

    $this->logger->debug('RssLivestreamUrlFetcher: got livestream URL', ['livestreamUrl' => $livestreamUrl]);
    return $livestreamUrl;
  }
}
