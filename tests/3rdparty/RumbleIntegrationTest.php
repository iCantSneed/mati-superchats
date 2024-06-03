<?php

declare(strict_types=1);

namespace Mati;

use Mati\Dto\RumbleChat\RumbleChatData;
use Mati\Rumble\ChatClient;
use Mati\Rumble\ChatUrlFetcher;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class RumbleIntegrationTest extends KernelTestCase
{
  private static string $livestreamUrl;

  public static function setUpBeforeClass(): void
  {
    self::$livestreamUrl = $_ENV['TEST_LIVESTREAM_URL'];
  }

  public function testGetChatMessages(): void
  {
    /** @var ChatUrlFetcher */ $chatUrlFetcher = self::getContainer()->get(ChatUrlFetcher::class);
    $chatUrlAndId = $chatUrlFetcher->fetchChatUrl(self::$livestreamUrl);
    self::assertNotNull($chatUrlAndId);
    [$chatUrl] = $chatUrlAndId;

    /** @var ChatClient */ $chatClient = self::getContainer()->get(ChatClient::class);
    $rumbleChatData = $chatClient->readData($chatUrl)->current();
    self::assertInstanceOf(RumbleChatData::class, $rumbleChatData);
    self::assertNotEmpty($rumbleChatData->messages);
    self::assertNotEmpty($rumbleChatData->users);
  }
}
