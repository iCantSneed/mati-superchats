<?php

declare(strict_types=1);

namespace Mati\Dto\RumbleChat;

use Symfony\Component\Serializer\Attribute\SerializedPath;

final class Message
{
  public function __construct(
    public string $id,
    public \DateTimeImmutable $time,
    public string $userId,
    public string $text,
    #[SerializedPath('[rant][price_cents]')]
    public ?int $rantPriceCents = null,
  ) {
    // Do nothing.
  }
}
