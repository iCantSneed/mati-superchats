<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Entity\Superchat;
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

    $superchats = $this->superchatCache->getLatestSuperchats()->superchats;
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
    $superchat = (null !== $message) ? @unserialize($message) : null;
    if (!$superchat instanceof Superchat) {
      return;
    }

    if ($superchat->getStream()->getId() === $lastStreamId) {
      $superchatTemplate = $this->twig->render('superchat/append_superchat.html.twig', [
        'superchat' => $superchat,
        'streamHtmlId' => StreamSuperchats::htmlId($superchat->getStream()),
      ]);
      self::transmitSseMessage($superchatTemplate);
    } else {
      $this->transmitLatestSuperchats([$superchat]);
      $lastStreamId = $superchat->getStream()->getId();
    }
  }

  /**
   * @param non-empty-list<Superchat> $superchats
   */
  private function transmitLatestSuperchats(array $superchats): void
  {
    $latestSuperchatsTemplate = $this->twig->render('superchat/show_latest_superchats.html.twig', [
      'superchats' => $superchats,
      'streamHtmlId' => StreamSuperchats::htmlId($superchats[0]->getStream()),
    ]);
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
