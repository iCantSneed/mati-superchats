<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Entity\Superchat;
use Mati\Twig\Components\StreamSuperchats;
use Twig\Environment;

final readonly class SuperchatRenderer
{
  public function __construct(private Environment $twig)
  {
    // Do nothing.
  }

  public function appendSuperchat(Superchat $superchat): string
  {
    return $this->twig->render('superchat/append_superchat.html.twig', [
      'superchat' => $superchat,
      'streamHtmlId' => StreamSuperchats::htmlId($superchat->getStream()),
    ]);
  }

  /**
   * @param non-empty-list<Superchat> $superchats
   */
  public function showLatestSuperchats(array $superchats): string
  {
    return $this->twig->render('superchat/show_latest_superchats.html.twig', [
      'superchats' => $superchats,
      'streamHtmlId' => StreamSuperchats::htmlId($superchats[0]->getStream()),
    ]);
  }

  /**
   * @param non-empty-list<Superchat> $superchats
   */
  public function prependSuperchats(array $superchats): string
  {
    return $this->twig->render('superchat/prepend_superchats.html.twig', [
      'superchats' => $superchats,
    ]);
  }
}
