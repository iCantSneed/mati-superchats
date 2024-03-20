<?php

declare(strict_types=1);

namespace Mati\Rumble;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ChatUrlFetcher
{
  public function __construct(
    private HttpClientInterface $httpClient,
    private LivestreamUrlCache $livestreamUrlCache,
    private LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  public function fetchChatUrl(string $livestreamUrl): ?string
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
    $matched = preg_match('/RumbleChat\("(.*?)", (\d+),/', $html, $matches);
    if (1 !== $matched) {
      $this->logger->warning('ChatUrlFetcher: chat URL components not found', ['matched' => $matched]);
      $this->livestreamUrlCache->storeLastFailedLivestreamUrl($livestreamUrl);

      return null;
    }

    \assert(isset($matches[1], $matches[2]));
    $chatBaseUrl = $matches[1];
    $chatId = $matches[2];
    $chatUrl = "{$chatBaseUrl}/chat/{$chatId}/stream";
    $this->logger->debug('ChatUrlFetcher: got chat URL', ['chatUrl' => $chatUrl]);

    return $chatUrl;
  }
}
