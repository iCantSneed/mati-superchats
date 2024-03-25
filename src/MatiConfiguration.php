<?php

declare(strict_types=1);

namespace Mati;

final readonly class MatiConfiguration
{
  public const string IPC_ADDRESS = '127.255.255.255';

  public const string PARAM_LIVESTREAM_LANDING_URL = '%env(LIVESTREAM_LANDING_URL)%';
  public const string PARAM_LIVESTREAM_RSS_URL = '%env(LIVESTREAM_RSS_URL)%';
  public const string PARAM_IPC_PORT = '%env(IPC_PORT)%';
}
