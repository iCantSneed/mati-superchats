<?php

declare(strict_types=1);

namespace Mati\Twig\Components;

use Mati\Entity\Stream;
use Mati\Entity\Superchat;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class StreamSuperchats
{
  /** @var non-empty-list<Superchat> */ public array $superchats;

  public function getStream(): Stream
  {
    return $this->superchats[0]->getStream();
  }

  public static function htmlId(Stream $stream): string
  {
    return "stream-{$stream->getId()}";
  }
}
