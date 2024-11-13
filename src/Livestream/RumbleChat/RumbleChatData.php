<?php

declare(strict_types=1);

namespace Mati\Livestream\RumbleChat;

use Symfony\Component\Serializer\Attribute\SerializedPath;

/**
 * FIXME assign empty array because invalid JSON will be incorrectly deserialized.
 */
final class RumbleChatData
{
  /**
   * @param Message[] $messages
   * @param User[]    $users
   */
  public function __construct(
    #[SerializedPath('[data][messages]')]
    public array $messages = [],
    #[SerializedPath('[data][users]')]
    public array $users = [],
  ) {
    // Do nothing.
  }
}
