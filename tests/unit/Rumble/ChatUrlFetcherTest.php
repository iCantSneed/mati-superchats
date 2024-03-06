<?php

declare(strict_types=1);

namespace Mati\Tests\Unit\Rumble;

use Mati\Rumble\ChatUrlFetcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal
 */
#[CoversClass(ChatUrlFetcher::class)]
final class ChatUrlFetcherTest extends TestCase
{
  private const MOCK_LIVESTREAM_URL = 'https://example.invalid/live';

  private MockHttpClient $mockHttpClient;
  private ChatUrlFetcher $chatUrlFetcher;

  protected function setUp(): void
  {
    $this->mockHttpClient = new MockHttpClient();
    $this->chatUrlFetcher = new ChatUrlFetcher($this->mockHttpClient, new NullLogger());
  }

  public function testGetChatUrl(): void
  {
    $response = new MockResponse('xXxXRumbleChat("https://other.invalid", 12345, xXxYyY...');
    $this->mockHttpClient->setResponseFactory($response);

    $chatUrl = $this->chatUrlFetcher->fetchChatUrl(self::MOCK_LIVESTREAM_URL);
    self::assertSame('https://other.invalid/chat/12345/stream', $chatUrl);
  }

  // TODO
  // public function testConnectionErrorReturnsNull(): void
  // {

  // }

  public function testStatusCodeNot200ReturnsNull(): void
  {
    $response = new MockResponse(
      'xXxXRumbleChat("https://other.invalid", 12345, xXxYyY...',
      ['http_code' => 500]
    );
    $this->mockHttpClient->setResponseFactory($response);

    $chatUrl = $this->chatUrlFetcher->fetchChatUrl(self::MOCK_LIVESTREAM_URL);
    self::assertNull($chatUrl);
  }

  public function testMissingRumbleChatTextReturnsNull(): void
  {
    $response = new MockResponse('bogus');
    $this->mockHttpClient->setResponseFactory($response);

    $chatUrl = $this->chatUrlFetcher->fetchChatUrl(self::MOCK_LIVESTREAM_URL);
    self::assertNull($chatUrl);
  }
}
