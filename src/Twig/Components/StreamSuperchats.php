<?php

declare(strict_types=1);

namespace Mati\Twig\Components;

use Mati\Entity\Stream;
use Mati\Entity\Superchat;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @psalm-suppress MissingConstructor all properties are set by UX Twig Components
 */
#[AsTwigComponent]
final class StreamSuperchats
{
  /** @var non-empty-list<Superchat> */ public array $superchats;

  /**
   * @psalm-suppress PossiblyUnusedMethod used by template
   */
  public function getStream(): Stream
  {
    return $this->superchats[0]->getStream();
  }

  public static function htmlId(Stream $stream): string
  {
    return "stream-{$stream->getId()}";
  }
}
