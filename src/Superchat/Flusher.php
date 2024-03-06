<?php

declare(strict_types=1);

namespace Mati\Superchat;

final readonly class Flusher implements FlusherInterface
{
  public function flush(): void
  {
    while (ob_get_level() > 0) {
      ob_end_flush();
    }
    flush();
  }
}
