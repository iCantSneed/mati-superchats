<?php

declare(strict_types=1);

namespace Mati;

final readonly class MatiConfiguration
{
  public const IPC_ADDRESS = '127.255.255.255';

  public const PARAM_LIVESTREAM_LANDING_URL = '%env(LIVESTREAM_LANDING_URL)%';
  public const PARAM_IPC_PORT = '%env(IPC_PORT)%';
  public const PARAM_IPC_SEMKEY = '%env(IPC_SEMKEY)%';
}
