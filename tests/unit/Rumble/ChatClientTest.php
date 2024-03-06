<?php

declare(strict_types=1);

namespace Mati\Tests\Unit\Rumble;

use Mati\Dto\RumbleChat\Message;
use Mati\Dto\RumbleChat\RumbleChatData;
use Mati\Rumble\ChatClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @internal
 */
#[CoversClass(ChatClient::class)]
final class ChatClientTest extends TestCase
{
  private const MOCK_CHAT_URL = 'https://example.invalid/chat';

  private static array $mockResponseInfo = ['response_headers' => ['Content-Type' => 'text/event-stream']];

  private MockObject&SerializerInterface $mockSerializer;
  private MockHttpClient $mockHttpClient;
  private LoggerInterface&MockObject $mockLogger;
  private ChatClient $chatClient;

  protected function setUp(): void
  {
    $this->mockSerializer = $this->createMock(SerializerInterface::class);
    $this->mockHttpClient = new MockHttpClient();
    $this->mockLogger = $this->createMock(LoggerInterface::class);
    $this->chatClient = new ChatClient($this->mockSerializer, $this->mockHttpClient, $this->mockLogger, 1);
  }

  public function testReadDataWithRumbleChatData(): void
  {
    $rawData = 'raw data';
    $rumbleChatData = self::createMockRumbleChatData('text');

    $this->setHttpClientBody("data: {$rawData}\n");
    $this->mockSerializer->expects(self::once())->method('deserialize')->with($rawData)->willReturn($rumbleChatData);

    self::assertSame(
      [$rumbleChatData],
      iterator_to_array($this->chatClient->readData(self::MOCK_CHAT_URL))
    );
  }

  public function testTimeoutContinuesAsUsual(): void
  {
    $this->setHttpClientBody('');
    $this->mockSerializer->expects(self::never())->method('deserialize');

    self::assertSame(
      [],
      iterator_to_array($this->chatClient->readData(self::MOCK_CHAT_URL))
    );
  }

  public function testEmptyDataContinuesAsUsualWithoutLogging(): void
  {
    $this->setHttpClientBody("data:\n");
    $this->mockSerializer->expects(self::never())->method('deserialize');
    $this->mockLogger->expects(self::never())->method('error');

    self::assertSame(
      [],
      iterator_to_array($this->chatClient->readData(self::MOCK_CHAT_URL))
    );
  }

  public function testInvalidDataContinuesAsUsualWithLogging(): void
  {
    $rawData = 'raw data';

    $this->setHttpClientBody("data: {$rawData}\n");
    $this->mockSerializer
      ->expects(self::once())
      ->method('deserialize')
      ->with($rawData)
      ->willThrowException(new UnexpectedValueException())
    ;
    $this->mockLogger->expects(self::once())->method('error');

    self::assertSame(
      [],
      iterator_to_array($this->chatClient->readData(self::MOCK_CHAT_URL))
    );
  }

  public function testMultipleChunks(): void
  {
    $rawData1 = 'raw data 1';
    $rumbleChatData1 = self::createMockRumbleChatData('text 1');
    $rawData2 = 'raw data 2';
    $rumbleChatData2 = self::createMockRumbleChatData('text 2');

    $body = static function () use ($rawData1, $rawData2): \Generator {
      yield "data: {$rawData1}\n\n";

      yield "data: {$rawData2}\n\n";
    };
    $this->setHttpClientBody($body());

    $this->mockSerializer->expects(self::exactly(2))->method('deserialize')->willReturnCallback(
      static fn (string $rawData) => match ($rawData) {
        $rawData1 => $rumbleChatData1,
        $rawData2 => $rumbleChatData2,
      }
    );

    self::assertSame(
      [$rumbleChatData1, $rumbleChatData2],
      iterator_to_array($this->chatClient->readData(self::MOCK_CHAT_URL))
    );
  }

  public function testChunkWithMultipleMessages(): void
  {
    $rawData1 = 'raw data 1';
    $rumbleChatData1 = self::createMockRumbleChatData('text 1');
    $rawData2a = 'raw data 2a';
    $rawData2b = 'raw data 2b';
    $rumbleChatData2 = self::createMockRumbleChatData('text 2');
    $rawData3 = 'raw data 3';
    $rumbleChatData3 = self::createMockRumbleChatData('text 3');

    $body = <<<EOL
    data: {$rawData1}

    data: {$rawData2a}
    data: {$rawData2b}

    data:

    data: {$rawData3}
    EOL;
    $this->setHttpClientBody($body);

    $this->mockSerializer->expects(self::exactly(3))->method('deserialize')->willReturnCallback(
      static fn (string $rawData) => match ($rawData) {
        $rawData1 => $rumbleChatData1,
        "{$rawData2a}\n{$rawData2b}" => $rumbleChatData2,
        $rawData3 => $rumbleChatData3,
      }
    );

    self::assertSame(
      [$rumbleChatData1, $rumbleChatData2, $rumbleChatData3],
      iterator_to_array($this->chatClient->readData(self::MOCK_CHAT_URL))
    );
  }

  /**
   * @param iterable<string|\Throwable>|string $body
   */
  private function setHttpClientBody(iterable|string $body): void
  {
    $response = new MockResponse($body, self::$mockResponseInfo);
    $this->mockHttpClient->setResponseFactory($response);
  }

  private static function createMockRumbleChatData(string $text): RumbleChatData
  {
    $rumbleChatData = new RumbleChatData();
    $message = new Message();
    $message->text = $text;
    $rumbleChatData->messages = [$message];

    return $rumbleChatData;
  }
}
