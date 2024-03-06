<?php

declare(strict_types=1);

namespace Mati\Rumble\Sse;

use Symfony\Component\Serializer\Attribute\SerializedPath;

/**
 * @psalm-suppress MissingConstructor
 */
final class SseData
{
  /** @var Message[] */
  #[SerializedPath('[data][messages]')]
  public array $messages;

  /** @var User[] */
  #[SerializedPath('[data][users]')]
  public array $users;
}
