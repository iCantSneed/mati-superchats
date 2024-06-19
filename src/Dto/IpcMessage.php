<?php

declare(strict_types=1);

namespace Mati\Dto;

use Mati\Entity\Superchat;

final class IpcMessage
{
  /** @var Superchat[] */ public array $superchats = [];

  public function __construct(
    public readonly string $livestreamUrl,
  ) {
    // Do nothing.
  }
}
