<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Entity\Superchat;
use Mati\Rumble\Sse\SseData;

final readonly class SuperchatConverter
{
  /**
   * @return iterable<Superchat>
   */
  public function extractSuperchats(SseData $sseData): iterable
  {
    $users = array_column($sseData->users, 'username', 'id');

    foreach ($sseData->messages as $message) {
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
