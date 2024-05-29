<?php

declare(strict_types=1);

namespace Mati\Dto;

use Mati\Entity\Stream;
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

  public function getStream(): Stream
  {
    \assert(isset($this->superchats[0]));

    return $this->superchats[0]->getStream();
  }
}
