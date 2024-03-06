<?php

declare(strict_types=1);

namespace Mati\Rumble;

use Mati\Dto\RumbleChat\RumbleChatData;
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
    HttpClientInterface $httpClient,
    private LoggerInterface $logger,
    private int $maxRetryCount = 2,
  ) {
    $this->client = new EventSourceHttpClient($httpClient);
  }

  /**
   * @return \Iterator<RumbleChatData>
   */
  public function readData(string $chatUrl): \Iterator
  {
    $retryCount = 0;
    while (true) {
      $source = $this->client->connect($chatUrl);
      foreach ($this->client->stream($source, 270) as $chunk) { // 4.5 minutes
        if ($chunk->isTimeout()) {
          $this->logger->warning('ChatClient: chunk timeout');
          if ($this->shouldCloseConnection($retryCount)) {
            return;
          }

          continue;
        }

        if ($chunk->isLast()) {
          $this->logger->notice('ChatClient: no more data');

          break;
        }

        if (!$chunk instanceof ServerSentEvent) {
          $this->logger->debug('ChatClient: chunk is not a SSE');

          continue;
        }

        $retryCount = 0;

        $rawData = $chunk->getData();
        $this->logger->debug('ChatClient: got SSE data', ['rawData' => $rawData]);
        if (empty($rawData)) {
          continue;
        }

        try {
          $rumbleChatData = $this->serializer->deserialize($rawData, RumbleChatData::class, 'json');
          $this->logger->debug('ChatClient: deserialized chunk', ['rumbleChatData' => $rumbleChatData]);

          yield $rumbleChatData;
        } catch (UnexpectedValueException $e) {
          $this->logger->error('ChatClient: chunk cannot be deserialized', ['exception' => $e]);

          continue;
        }
      }

      $this->logger->warning('ChatClient: connection was closed');
      if ($this->shouldCloseConnection($retryCount)) {
        return;
      }
    }
  }

  private function shouldCloseConnection(int &$retryCount): bool
  {
    ++$retryCount;
    if ($retryCount >= $this->maxRetryCount) {
      $this->logger->notice('ChatClient: max retry count reached, closing connection');

      return true;
    }

    return false;
  }
}
