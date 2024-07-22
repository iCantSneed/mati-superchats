<?php

declare(strict_types=1);

namespace Mati\Rumble;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class LivestreamUrlFetcher
{
  public function __construct(
    private HttpClientInterface $httpClient,
    private LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  public function fetchLivestreamUrl(string $livestreamLandingUrl): ?string
  {
    $response = $this->httpClient->request('GET', $livestreamLandingUrl);

    $statusCode = $response->getStatusCode();
    if (200 !== $statusCode) {
      $this->logger->error('LivestreamUrlFetcher: failed to get livestream landing page', [
        'statusCode' => $statusCode,
      ]);

      return null;
    }

    $html = $response->getContent(false);
    $crawler = new Crawler($html, $livestreamLandingUrl);

    $liveThumbnail = $crawler->filter('.thumbnail__thumb--live,.thumbnail__thumb--upcoming')->first();
    if (0 === $liveThumbnail->count()) {
      $this->logger->warning('LivestreamUrlFetcher: no live thumbnail present');

      return null;
    }

    $livestreamLink = $liveThumbnail->filter('a')->first();
    if (0 === $livestreamLink->count()) {
      $this->logger->error('LivestreamUrlFetcher: live thumbnail has no link');

      return null;
    }

    $livestreamUrl = $livestreamLink->link()->getUri();
    $this->logger->debug('LivestreamUrlFetcher: got livestream URL', ['livestreamUrl' => $livestreamUrl]);

    return $livestreamUrl;
  }
}
