<?php

declare(strict_types=1);

namespace Mati;

use Mati\Livestream\ChatClient;
use Mati\Livestream\LivestreamInfoFetcher;
use Mati\Livestream\RumbleChat\RumbleChatData;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class RumbleIntegrationTest extends KernelTestCase
{
  private static string $livestreamLandingUrl;

  public static function setUpBeforeClass(): void
  {
    self::$livestreamLandingUrl = $_ENV['TEST_LIVESTREAM_LANDING_URL'];
  }

  public function testGetChatMessages(): void
  {
    /** @var LivestreamInfoFetcher */ $livestreamInfoFetcher = self::getContainer()->get(LivestreamInfoFetcher::class);
    $livestreamInfo = $livestreamInfoFetcher->fetchLivestreamInfo(self::$livestreamLandingUrl);
    self::assertNotNull($livestreamInfo);

    /** @var ChatClient */ $chatClient = self::getContainer()->get(ChatClient::class);
    foreach ($chatClient->readData($livestreamInfo->chatUrl) as $rumbleChatData) {
      if (null === $rumbleChatData) {
        continue;
      }
      self::assertInstanceOf(RumbleChatData::class, $rumbleChatData);
      self::assertNotEmpty($rumbleChatData->messages);
      self::assertNotEmpty($rumbleChatData->users);

      break;
    }
  }
}
