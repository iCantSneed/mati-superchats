<?php

declare(strict_types=1);

namespace Mati\Dto;

use Mati\Entity\Superchat;

final class SuperchatsData
{
  /**
   * @param Superchat[] $superchats
   */
  public function __construct(
    public array $superchats,
  ) {
    // Do nothing.
  }
}
