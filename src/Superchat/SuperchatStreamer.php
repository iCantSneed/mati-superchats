<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Ipc\IpcClient;
use Mati\Twig\Components\StreamSuperchats;
use Twig\Environment;

final readonly class SuperchatStreamer
{
  public function __construct(
    private IpcClient $ipcClient,
    private SuperchatCache $superchatCache,
    private Environment $twig,
  ) {
    // Do nothing.
  }

  public function streamEvents(): void
  {
    if (!$this->ipcClient->init()) {
      return;
    }

    $latestSuperchats = $this->superchatCache->getLatestSuperchats();
    $latestSuperchatsTemplate = $this->twig->render('superchat/show_latest_superchats.html.twig', [
      'superchatsData' => $latestSuperchats,
      'streamHtmlId' => StreamSuperchats::htmlId($latestSuperchats->getStream()),
    ]);
    self::transmitSseMessage($latestSuperchatsTemplate);

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
