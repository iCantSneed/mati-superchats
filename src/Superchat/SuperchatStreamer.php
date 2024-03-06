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

    flush();
    foreach ($this->ipcClient->receive() as $message) {
      $lines = explode(separator: "\n", string: $message);
      foreach ($lines as $line) {
        echo "data: {$line}\n";
      }
      echo "\n";
      flush();
    }
  }
}
