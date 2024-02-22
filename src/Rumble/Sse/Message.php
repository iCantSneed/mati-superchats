<?php

declare(strict_types=1);

namespace Mati\Rumble\Sse;

use Symfony\Component\Serializer\Attribute\SerializedPath;

/**
 * @psalm-suppress MissingConstructor
 */
final class Message
{
  public string $id;
  public \DateTimeImmutable $time;
  public string $userId;
  public string $text;

  #[SerializedPath('[rant][price_cents]')]
  public ?int $rantPriceCents = null;
}
