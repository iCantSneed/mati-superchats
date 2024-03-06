<?php

declare(strict_types=1);

namespace Mati\Rumble\Sse;

use Symfony\Component\Serializer\Attribute\SerializedPath;

/**
 * FIXME assign empty array because invalid JSON will be incorrectly deserialized.
 *
 * @psalm-suppress MissingConstructor
 */
final class SseData
{
  /** @var Message[] */
  #[SerializedPath('[data][messages]')]
  public array $messages = [];

  /** @var User[] */
  #[SerializedPath('[data][users]')]
  public array $users = [];
}
