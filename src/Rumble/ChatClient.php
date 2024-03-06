<?php

declare(strict_types=1);

namespace Mati\Rumble;

use Mati\Rumble\Sse\SseData;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ChatClient
{
  private EventSourceHttpClient $client;

  public function __construct(
    private SerializerInterface $serializer,
    private LoggerInterface $logger,
    HttpClientInterface $httpClient,
  ) {
    $this->client = new EventSourceHttpClient($httpClient);
  }

  /**
   * @return iterable<SseData>
   */
  public function readData(string $chatUrl): iterable
  {
    $source = $this->client->connect($chatUrl);
    while ($source) {
      foreach ($this->client->stream($source, 270) as $chunk) { // 4.5 minutes
        if ($chunk->isTimeout()) {
          continue;
        }

        if ($chunk->isLast()) {
          $this->logger->notice('ChatClient: no more data');

          return;
        }

        if (!$chunk instanceof ServerSentEvent) {
          $this->logger->debug('ChatClient: chunk is not a SSE');

          continue;
        }

        $rawData = $chunk->getData();
        $this->logger->debug('ChatClient: got SSE data', ['rawData' => $rawData]);

        try {
          $sseData = $this->serializer->deserialize($rawData, SseData::class, 'json');
          $this->logger->debug('ChatClient: deserialized chunk', ['sseData' => $sseData]);

          yield $sseData;
        } catch (UnexpectedValueException $e) {
          $this->logger->error('ChatClient: chunk cannot be deserialized', ['exception' => $e]);

          continue;
        }
      }
    }
  }
}
