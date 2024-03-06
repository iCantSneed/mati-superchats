<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Ipc\IpcClientInterface;

final readonly class SuperchatStreamer
{
  public function __construct(
    private IpcClientInterface $ipcClient,
    private FlusherInterface $flusher,
  ) {
    // Do nothing.
  }

  public function streamEvents(): void
  {
    if (!$this->ipcClient->init()) {
      return;
    }

    echo "\n"; // TODO send cached superchats
    $this->flusher->flush();
    foreach ($this->ipcClient->receive() as $message) {
      $lines = explode(separator: "\n", string: $message);
      foreach ($lines as $line) {
        echo "data: {$line}\n";
      }
      echo "\n";
      $this->flusher->flush();

      if (0 !== connection_aborted()) {
        return;
      }
    }
  }
}
