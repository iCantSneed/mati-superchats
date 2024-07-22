<?php

declare(strict_types=1);

namespace Mati;

use Mati\Dto\RumbleChat\RumbleChatData;
use Mati\Rumble\ChatClient;
use Mati\Rumble\ChatUrlFetcher;
use Mati\Rumble\LivestreamUrlFetcher;
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
    /** @var LivestreamUrlFetcher */ $livestreamUrlFetcher = self::getContainer()->get(LivestreamUrlFetcher::class);
    $livestreamUrl = $livestreamUrlFetcher->fetchLivestreamUrl(self::$livestreamLandingUrl);
    self::assertNotNull($livestreamUrl);
    /** @var ChatUrlFetcher */ $chatUrlFetcher = self::getContainer()->get(ChatUrlFetcher::class);
    $chatUrlAndId = $chatUrlFetcher->fetchChatUrl($livestreamUrl);
    self::assertNotNull($chatUrlAndId);
    [$chatUrl] = $chatUrlAndId;

    /** @var ChatClient */ $chatClient = self::getContainer()->get(ChatClient::class);
    foreach ($chatClient->readData($chatUrl) as $rumbleChatData) {
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
