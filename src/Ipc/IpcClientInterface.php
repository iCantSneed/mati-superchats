<?php

declare(strict_types=1);

namespace Mati\Ipc;

interface IpcClientInterface
{
  public function init(): bool;

  /**
   * @return iterable<string>
   */
  public function receive(): iterable;
}
