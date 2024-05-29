<?php

declare(strict_types=1);

namespace Mati\Twig\Components;

use Mati\Dto\SuperchatsData;
use Mati\Entity\Stream;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent]
final class StreamSuperchats
{
  public SuperchatsData $superchatsData;
  public Stream $stream;

  #[PostMount]
  public function postMount(): void
  {
    $this->stream = $this->superchatsData->getStream();
  }

  public static function htmlId(Stream $stream): string
  {
    return "stream-{$stream->getId()}";
  }
}
