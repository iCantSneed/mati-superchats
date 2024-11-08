<?php

declare(strict_types=1);

namespace Mati\Twig\Components;

use Mati\Entity\Superchat;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @psalm-suppress MissingConstructor all properties are set by UX Twig Components
 */
#[AsTwigComponent]
final class SuperchatMessage
{
  public Superchat $superchat;

  public function getPrice(): string
  {
    $priceCents = $this->superchat->getPriceCents();
    $dollars = $priceCents / 100;
    $cents = $priceCents % 100;

    return \sprintf('%d.%02d', $dollars, $cents);
  }
}
