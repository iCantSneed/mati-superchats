<?php

declare(strict_types=1);

namespace Mati\Livestream;

final readonly class LivestreamInfo
{
  public function __construct(
    public string $livestreamUrl,
    public string $chatUrl,
    public int $chatId,
  ) {
    // Do nothing.
  }
}
