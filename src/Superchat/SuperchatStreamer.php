<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Ipc\IpcClient;

final readonly class SuperchatStreamer
{
  public function __construct(private IpcClient $ipcClient)
  {
    // Do nothing.
  }

  public function streamEvents(): void
  {
    if (!$this->ipcClient->init()) {
      return;
    }

    // TODO send cached superchats
    self::flush();

    foreach ($this->ipcClient->receive() as $message) {
      if (null !== $message) {
        $lines = explode(separator: "\n", string: $message);
        foreach ($lines as $line) {
          echo "data: {$line}\n";
        }
      }

      self::flush();

      if (0 !== connection_aborted()) {
        return;
      }
    }
  }

  private static function flush(): void
  {
    echo "\n";
    while (ob_get_level() > 0) {
      ob_end_flush();
    }
    flush();
  }
}
