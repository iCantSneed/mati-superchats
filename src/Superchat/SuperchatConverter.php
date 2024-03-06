<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Dto\RumbleChat\RumbleChatData;
use Mati\Entity\Superchat;

final readonly class SuperchatConverter
{
  /**
   * @return iterable<Superchat>
   */
  public function extractSuperchats(RumbleChatData $rumbleChatData): iterable
  {
    $users = array_column($rumbleChatData->users, 'username', 'id');

    foreach ($rumbleChatData->messages as $message) {
      if (null !== $message->rantPriceCents) {
        $username = $users[$message->userId] ?? '<UNKNOWN>';
        $superchat = (new Superchat())
          ->setId((int) $message->id)
          ->setUsername($username)
          ->setPriceCents($message->rantPriceCents)
          ->setMessage($message->text)
          ->setCreated($message->time)
        ;

        yield $superchat;
      }
    }
  }
}
