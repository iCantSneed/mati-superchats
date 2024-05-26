<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Ipc\IpcClient;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class SuperchatStreamer
{
  public function __construct(
    private IpcClient $ipcClient,
    private SuperchatCache $superchatCache,
    private SerializerInterface $serializer,
  ) {
    // Do nothing.
  }

  public function streamEvents(): void
  {
    if (!$this->ipcClient->init()) {
      return;
    }

    $latestSuperchats = $this->superchatCache->getLatestSuperchats();
    self::transmitSseMessage($this->serializer->serialize($latestSuperchats, 'json'));

    foreach ($this->ipcClient->receive() as $message) {
      self::transmitSseMessage($message);

      if (0 !== connection_aborted()) {
        return;
      }
    }
  }

  private static function transmitSseMessage(?string $message): void
  {
    if (null !== $message) {
      $lines = explode(separator: "\n", string: $message);
      foreach ($lines as $line) {
        echo "data: {$line}\n";
      }
    }

    echo "\n";
    while (ob_get_level() > 0) {
      ob_end_flush();
    }
    flush();
  }
}
