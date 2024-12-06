<?php

declare(strict_types=1);

namespace Mati\Ipc;

use Mati\Entity\Superchat;

final class IpcMessage
{
  /** @var Superchat[] */ public array $superchats = [];
}
