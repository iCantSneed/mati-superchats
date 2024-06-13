<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Dto\IpcMessage;
use Mati\Entity\Superchat;
use Mati\Ipc\IpcClient;

final readonly class SuperchatStreamer
{
  public function __construct(
    private IpcClient $ipcClient,
    private SuperchatCache $superchatCache,
    private SuperchatRenderer $renderer,
  ) {
    // Do nothing.
  }

  public function streamEvents(): void
  {
    if (!$this->ipcClient->init()) {
      return;
    }

    $superchats = $this->superchatCache->getLatestSuperchats();
    $this->transmitLatestSuperchats($superchats);
    $lastStreamId = $superchats[0]->getStream()->getId();

    foreach ($this->ipcClient->receive() as $message) {
      $this->transmitIpcMessage($message, $lastStreamId);

      if (0 !== connection_aborted()) {
        return;
      }
    }
  }

  private function transmitIpcMessage(?string $message, int &$lastStreamId): void
  {
    $ipcMessage = (null !== $message) ? @unserialize($message) : null;
    if (!$ipcMessage instanceof IpcMessage) {
      return;
    }

    // TODO remove this
    if (!isset($ipcMessage->superchats[0])) {
      return;
    }

    if ($ipcMessage->superchats[0]->getStream()->getId() === $lastStreamId) {
      $superchatTemplate = $this->renderer->appendSuperchat($ipcMessage->superchats[0]);
      self::transmitSseMessage($superchatTemplate);
    } else {
      $this->transmitLatestSuperchats([$ipcMessage->superchats[0]]);
      $lastStreamId = $ipcMessage->superchats[0]->getStream()->getId();
    }
  }

  /**
   * @param non-empty-list<Superchat> $superchats
   */
  private function transmitLatestSuperchats(array $superchats): void
  {
    $latestSuperchatsTemplate = $this->renderer->showLatestSuperchats($superchats);
    self::transmitSseMessage($latestSuperchatsTemplate);
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
