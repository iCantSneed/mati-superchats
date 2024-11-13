<?php

declare(strict_types=1);

namespace Mati\Livestream;

use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class LivestreamInfoFetcher
{
  public function __construct(
    private HttpClientInterface $httpClient,
    private LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  public function fetchLivestreamInfo(string $livestreamLandingUrl): ?LivestreamInfo
  {
    $livestreamUrl = $this->fetchLivestreamUrl($livestreamLandingUrl);
    if (null === $livestreamUrl) {
      return null;
    }

    return $this->parseLivestreamPage($livestreamUrl);
  }

  private function fetchLivestreamUrl(string $livestreamLandingUrl): ?string
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

  private function parseLivestreamPage(string $livestreamUrl): ?LivestreamInfo
  {
    $response = $this->httpClient->request('GET', $livestreamUrl);

    $statusCode = $response->getStatusCode();
    if (200 !== $statusCode) {
      $this->logger->error('ChatUrlFetcher: failed to get livestream landing page', [
        'statusCode' => $statusCode,
      ]);

      return null;
    }

    $html = $response->getContent(false);
    $matched = preg_match('/RumbleChat\("(.*?)",.*?, (\d+),/', $html, $matches);
    if (1 !== $matched) {
      $this->logger->warning('ChatUrlFetcher: chat URL components not found', ['matched' => $matched]);

      return null;
    }

    \assert(isset($matches[1], $matches[2]));
    $chatBaseUrl = $matches[1];
    $chatId = $matches[2];
    $chatUrl = "{$chatBaseUrl}/chat/{$chatId}/stream";
    $this->logger->debug('ChatUrlFetcher: got chat URL', ['chatUrl' => $chatUrl]);

    return new LivestreamInfo($livestreamUrl, $chatUrl, (int) $chatId);
  }
}
