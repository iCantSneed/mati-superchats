<?php

declare(strict_types=1);

namespace Mati;

final readonly class MatiConfiguration
{
  public const string IPC_ADDRESS = '127.255.255.255';

  public const string ENV_LIVESTREAM_RSS_URL = 'LIVESTREAM_RSS_URL';
  public const string ENV_IPC_PORT = 'IPC_PORT';
  public const string ENV_LIVESTREAM_URL_CACHE_KEY = 'LIVESTREAM_URL_CACHE_KEY';
  public const string ENV_SUPERCHATS_CACHE_KEY = 'SUPERCHATS_CACHE_KEY';
}
