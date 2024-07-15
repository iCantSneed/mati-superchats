<?php

declare(strict_types=1);

namespace Mati\Dto\RumbleChat;

final class User
{
  public function __construct(
    public string $id,
    public string $username,
  ) {
    // TODO these parameters are used by SuperchatConverter, but psalm is complaining that they're unused.
    // This is why these next two lines are here.
    $this->id;
    $this->username;
  }
}
