<?php

declare(strict_types=1);

namespace Mati\Tests\Unit\Superchat;

use Mati\Dto\RumbleChat\Message;
use Mati\Dto\RumbleChat\RumbleChatData;
use Mati\Dto\RumbleChat\User;
use Mati\Entity\Superchat;
use Mati\Superchat\SuperchatConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SuperchatConverter::class)]
final class SuperchatConverterTest extends TestCase
{
  private SuperchatConverter $superchatConverter;
  private \DateTimeImmutable $now;

  protected function setUp(): void
  {
    $this->superchatConverter = new SuperchatConverter();
    $this->now = new \DateTimeImmutable();
  }

  public function testExtractSingleSuperchat(): void
  {
    $chatData = new RumbleChatData();
    $chatData->messages = [$this->createMessage(123, 'uid', 500)];
    $chatData->users = [$this->createUser('uid')];

    $expectedSuperchat = (new Superchat())
      ->setId(123)
      ->setUsername('User uid')
      ->setPriceCents(500)
      ->setMessage('Text 123')
      ->setCreated($this->now)
    ;

    self::assertSame(
      [$expectedSuperchat],
      iterator_to_array($this->superchatConverter->extractSuperchats($chatData))
    );
  }

  public function testExtractSuperchatWithNoPriceReturnsNothing(): void
  {
    $chatData = new RumbleChatData();
    $chatData->messages = [$this->createMessage(123, 'uid', null)];
    $chatData->users = [$this->createUser('uid')];

    self::assertEmpty(iterator_to_array($this->superchatConverter->extractSuperchats($chatData)));
  }

  public function testExtractMultipleSuperchats(): void
  {
    $now1 = $this->now->add(new \DateInterval('PT1S'));
    $now2 = $this->now->add(new \DateInterval('PT2S'));

    $chatData = new RumbleChatData();
    $chatData->messages = [
      $this->createMessage(1, 'id1', 500),
      $this->createMessage(2, 'id2', 750, $now1),
      $this->createMessage(3, 'id1', 1000, $now2),
    ];
    $chatData->users = [$this->createUser('id1'), $this->createUser('id2')];

    $expectedSuperchats = [
      (new Superchat())
        ->setId(1)
        ->setUsername('User id1')
        ->setPriceCents(500)
        ->setMessage('Text 1')
        ->setCreated($this->now),
      (new Superchat())
        ->setId(2)
        ->setUsername('User id2')
        ->setPriceCents(750)
        ->setMessage('Text 2')
        ->setCreated($now1),
      (new Superchat())
        ->setId(3)
        ->setUsername('User id1')
        ->setPriceCents(1000)
        ->setMessage('Text 3')
        ->setCreated($now2),
    ];

    self::assertSame(
      $expectedSuperchats,
      iterator_to_array($this->superchatConverter->extractSuperchats($chatData))
    );
  }

  private function createMessage(
    int $messageId,
    string $userId,
    ?int $priceCents,
    ?\DateTimeImmutable $time = null,
  ): Message {
    $message = new Message();
    $message->id = (string) $messageId;
    $message->time = $time ?? $this->now;
    $message->userId = $userId;
    $message->text = "Text {$messageId}";
    $message->rantPriceCents = $priceCents;

    return $message;
  }

  private function createUser(string $userId): User
  {
    $user = new User();
    $user->id = $userId;
    $user->username = "User {$userId}";

    return $user;
  }
}
