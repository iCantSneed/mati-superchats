<?php

declare(strict_types=1);

namespace Mati\Superchat;

use Mati\Entity\Superchat;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class SuperchatRenderer
{
  public function __construct(
    private SerializerInterface $serializer,
  ) {
    // Do nothing.
  }

  public function toJson(Superchat $superchat): string
  {
    return $this->serializer->serialize($superchat, 'json');
  }
}
