<?php

declare(strict_types=1);

namespace Mati\Livestream;

use Mati\Livestream\RumbleChat\RumbleChatData;
use Mati\MatiConfiguration;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ChatClient
{
  private const int MAX_RETRY_COUNT = 2;

  private EventSourceHttpClient $client;

  public function __construct(
    private SerializerInterface $serializer,
    private LoggerInterface $logger,
    HttpClientInterface $httpClient,
  ) {
    $this->client = new EventSourceHttpClient($httpClient);
  }

  /**
   * @return \Iterator<?RumbleChatData>
   */
  public function readData(string $chatUrl): \Iterator
  {
    $retryCount = 0;
    while (true) {
      $source = $this->client->connect($chatUrl);
      foreach ($this->client->stream($source, MatiConfiguration::LIVE_CHAT_TIMEOUT_SECONDS) as $chunk) {
        if ($chunk->isTimeout()) {
          $this->logger->warning('ChatClient: chunk timeout');

          yield null;

          continue;
        }

        if ($chunk->isLast()) {
          $this->logger->notice('ChatClient: no more data');

          break;
        }

        if (!$chunk instanceof ServerSentEvent) {
          $this->logger->debug('ChatClient: chunk is not a SSE');

          yield null;

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
      ++$retryCount;
      if ($retryCount >= self::MAX_RETRY_COUNT) {
        $this->logger->notice('ChatClient: max retry count reached, closing connection');

        return;
      }
    }
  }
}
