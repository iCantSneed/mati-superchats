<?php

declare(strict_types=1);

namespace Mati\Rumble\Sse;

/**
 * @psalm-suppress MissingConstructor
 */
final class User
{
  /** @psalm-suppress PossiblyUnusedProperty (used by SuperchatConverter) */
  public string $id;

  /** @psalm-suppress PossiblyUnusedProperty (used by SuperchatConverter) */
  public string $username;
}
